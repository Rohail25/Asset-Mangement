<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetRegister;
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
        $files = AssetRegister::orderByDesc('created_at')->latest()
                    ->get();
        // Need clients for the filter dropdown
        $client = Client::where('id', $client_id)->first();

        // Auditors to attribute the upload (migration requires audit_id)
        $auditors = Auditor::where('status', 'active')
                    ->orderBy('name')->get(['id','name']);
        }
        else{
             // List newest first
        $files = AssetRegister::orderByDesc('created_at')->where('audit_id', Auth::guard('auditor')->id())->where('client_id', $client_id)->latest()
                    ->get();
        // Need clients for the filter dropdown
        $client = Client::where('id', $client_id)->first();

        // Auditors to attribute the upload (migration requires audit_id)
        $auditors = Auditor::where('status', 'active')
                    ->orderBy('name')->get(['id','name']);
        }
      
        // dd($client);

        return view('admin.pages.asset_register.index', compact('client','files','auditors'));
    }

    /**
     * Upload CSV, detect header row (first line), store headings JSON.
     */
    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'file'       => ['required','file','mimes:csv,txt','max:10240'], // 10MB
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
            if ($h !== '') { $headings[] = $h; }
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
            ->with('ok', 'Headings saved from: '.$original);
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
     * Utility: read only the very first CSV row from a stream.
     * - Tries standard CSV parsing with comma
     * - Skips BOM if present
     */
    private function readCsvFirstRow($stream): array
    {
        // Skip UTF-8 BOM if present
        $bom = fread($stream, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            // Not BOM, rewind to start
            rewind($stream);
        }

        // Read the first row. If your CSVs sometimes use semicolon, you can try detect.
        $row = fgetcsv($stream, 0, ','); // delimiter ','

        // If row is a single cell with semicolons, try ';'
        if (is_array($row) && count($row) === 1 && str_contains($row[0] ?? '', ';')) {
            rewind($stream);
            // re-skip BOM check
            $bom = fread($stream, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($stream);
            $row = fgetcsv($stream, 0, ';');
        }

        return is_array($row) ? $row : [];
    }

}
