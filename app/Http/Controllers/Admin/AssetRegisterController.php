<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetRegister;
use App\Models\AssetRegisterRow;
use App\Models\Auditor;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AssetRegisterController extends Controller
{
    public function index($client_id)
    {
        if (Auth::guard('web')->check()) {
            // Authenticated as auditor
            // List newest first
            $files = AssetRegister::orderByDesc('created_at')->where('client_id', $client_id)->latest()
                ->get();
            // Need clients for the filter dropdown
            $client = Client::where('id', $client_id)->first();

            // Auditors to attribute the upload (migration requires audit_id)
            $auditors = Auditor::where('status', 'active')
                ->orderBy('name')->get(['id', 'name']);
        } else {
            // List newest first
            $files = AssetRegister::orderByDesc('created_at')->where('audit_id', Auth::guard('auditor')->id())->where('client_id', $client_id)->latest()
                ->get();
            // Need clients for the filter dropdown
            $client = Client::where('id', $client_id)->first();

            // Auditors to attribute the upload (migration requires audit_id)
            $auditors = Auditor::where('status', 'active')
                ->orderBy('name')->get(['id', 'name']);
        }

        // dd($client);

        return view('admin.pages.asset_register.index', compact('client', 'files', 'auditors'));
    }

    /**
     * Upload CSV, detect header row (first line), store headings JSON.
     */
    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'file'       => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // 10MB
        ]);

        // Read file contents
        $file      = $data['file'];
        $auditorId = Auth::guard('auditor')->id();
        $original  = $file->getClientOriginalName();
        $stream    = fopen($file->getRealPath(), 'r');

        if ($stream === false) {
            return back()->withErrors('Could not read uploaded file.');
        }

        // Handle potential UTF-8 BOM and get first row only
        $firstRow = $this->readCsvFirstRow($stream);
        fclose($stream);

        if (empty($firstRow)) {
            return back()->withErrors('CSV appears empty or has no header row.');
        }

        // Normalize: trim items, drop empty columns, preserve order as in CSV
        $headings = [];
        foreach ($firstRow as $h) {
            $h = trim((string)$h);
            if ($h !== '') {
                $headings[] = $h;
            }
        }

        if (empty($headings)) {
            return back()->withErrors('No valid headings detected in the first row.');
        }

        AssetRegister::create([
            'client_id'       => $client->id,
            'audit_id'        => $auditorId, // who uploaded it
            'headings'        => json_encode($headings), // <— manual encode
            'source_filename' => $original,
        ]);

        return redirect()
            ->route(Auth::guard('auditor')->check() ? 'auditor.register.index' : 'admin.register.index', $client->id)
            ->with('ok', 'Headings saved from: ' . $original);
    }

    /**
     * Delete a single heading set (soft delete).
     */
    public function destroy(Client $client, AssetRegister $register)
    {
        // Ensure it belongs to this client
        if ($register->client_id !== $client->id) {
            abort(Response::HTTP_FORBIDDEN, 'Wrong client.');
        }

        $register->delete(); // soft-delete

        return back()->with('ok', 'Upload deleted.');
    }
    /**
     * Import REGISTER DATA CSV that matches a specific heading set (asset_registers).
     * - Reads the FIRST line as header to map to the saved headings
     * - Then imports all subsequent lines into asset_register_rows
     */
    public function rowsStore(Request $request, Client $client, AssetRegister $register)
    {
        abort_if($register->client_id !== (int)$client->id, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'], // 20MB
        ]);

        // Load the saved headings for this register
        $headings = is_array($register->headings) ? $register->headings : json_decode($register->headings, true);
        if (empty($headings) || !is_array($headings)) {
            return back()->withErrors('No headings found for this register set.');
        }

        $file     = $request->file('file');
        $origName = $file->getClientOriginalName();
        $stream   = fopen($file->getRealPath(), 'r');
        if ($stream === false) return back()->withErrors('Could not read uploaded file.');

        // Read header row from the CSV being imported
        $csvHeader = $this->readCsvFirstRow($stream);
        if (empty($csvHeader)) {
            fclose($stream);
            return back()->withErrors('CSV appears empty or has no header row.');
        }

        // Normalize: trim strings
        $norm = fn($v) => strtoupper(trim((string)$v));
        $savedHeadingsNorm = array_map($norm, $headings);
        $csvHeaderNorm     = array_map($norm, $csvHeader);

        // Build column index mapping: for each saved heading, find its position in CSV header
        $colIndex = [];
        foreach ($savedHeadingsNorm as $i => $h) {
            // find same heading in the uploaded file header
            $pos = array_search($h, $csvHeaderNorm, true);
            if ($pos === false) {
                // allow loose matching (snake to spaces), optional
                $snake = str_replace('_', ' ', $h);
                $pos = array_search($snake, $csvHeaderNorm, true);
            }
            if ($pos === false) {
                fclose($stream);
                return back()->withErrors("Heading '{$headings[$i]}' not found in CSV header. Please upload a file with the same headings.");
            }
            $colIndex[$i] = $pos;
        }

        // Now read all remaining rows and insert
        $inserted = 0;
        $batch = [];
        $now = now();

        while (($row = fgetcsv($stream, 0, ',')) !== false) {
            // If delimiter issue, try ';'
            if (count($row) === 1 && str_contains($row[0] ?? '', ';')) {
                $row = str_getcsv($row[0], ';');
            }

            // Skip empty line
            $nonEmpty = array_filter($row, fn($v) => trim((string)$v) !== '');
            if (count($nonEmpty) === 0) continue;

            // Map to associative data by the saved headings order
            $payload = [];
            foreach ($headings as $i => $label) {
                $srcIndex = $colIndex[$i];
                $payload[$label] = isset($row[$srcIndex]) ? trim((string)$row[$srcIndex]) : null;
            }

            $batch[] = [
                'client_id'       => $client->id,
                'register_id'     => $register->id,
                'source_filename' => $origName,
                'data'            => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            // Chunk insert to avoid memory issues
            if (count($batch) >= 1000) {
                AssetRegisterRow::insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }
        fclose($stream);

        if (!empty($batch)) {
            AssetRegisterRow::insert($batch);
            $inserted += count($batch);
        }

        return back()->with('ok', "Imported {$inserted} register rows from {$origName}.");
    }

    /**
     * OPTIONAL: Delete all rows for a given register set (careful!)
     */
    public function rowsDestroy(Client $client, AssetRegister $register)
    {
        abort_if($register->client_id !== (int)$client->id, 403);
        AssetRegisterRow::where('client_id', $client->id)->where('register_id', $register->id)->delete();
        return back()->with('ok', 'All rows for this register set have been deleted.');
    }

    /**
     * Reuse your header reader from earlier
     */
    private function readCsvFirstRow($stream): array
    {
        // Skip BOM
        $bom = fread($stream, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($stream);

        $row = fgetcsv($stream, 0, ',');
        if (is_array($row) && count($row) === 1 && str_contains($row[0] ?? '', ';')) {
            rewind($stream);
            $bom = fread($stream, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($stream);
            $row = fgetcsv($stream, 0, ';');
        }
        return is_array($row) ? $row : [];
    }
}
