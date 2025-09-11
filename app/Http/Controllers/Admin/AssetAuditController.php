<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetAuditField;
use App\Models\AssetAuditFile;
use App\Models\AssetAuditRow;
use App\Models\AuditFieldOption;
use App\Models\Auditor;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\AssetRegister;
use App\Models\AssetRegisterRow;
use Illuminate\Support\Str;


class AssetAuditController extends Controller
{
    // Show schema builder UI
    public function schema(Client $client)
    {
        if (Auth::guard('web')->check()) {
            $fields = AssetAuditField::where('client_id', $client->id)
                ->orderBy('order_index')
                ->get();
        } else {
            $fields = AssetAuditField::where('client_id', $client->id)->where('auditor_id', Auth::guard('auditor')->id())
                ->orderBy('order_index')
                ->with('options')
                ->get();
        }
        return view('admin.pages.asset_audit.schema', compact('client', 'fields'));
    }

    // Create field
    public function fieldStore(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'        => [
                'required',
                'regex:/^[a-z0-9_]+$/i',
                'max:64',
                Rule::unique('asset_audit_fields', 'name')->where(fn($q) => $q->where('client_id', $client->id)->whereNull('deleted_at'))
            ],
            'label'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', Rule::in(['text', 'number', 'date', 'textarea', 'dropdown', 'checkbox'])],
            'required'    => ['nullable', 'boolean'],
            'scan_enabled' => ['nullable', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['client_id']   = $client->id;
        $data['required']    = (bool)($data['required'] ?? false);
        $data['scan_enabled'] = (bool)($data['scan_enabled'] ?? false);
        $data['order_index'] = (int)($data['order_index'] ?? 0);
        $data['auditor_id']    = Auth::guard('auditor')->id() ?? null;

        AssetAuditField::create($data);

        return back()->with('ok', 'Field added.');
    }

    // Delete field
    public function fieldDestroy(Client $client, AssetAuditField $field)
    {
        abort_if($field->client_id !== $client->id, 403);
        $field->delete();
        return back()->with('ok', 'Field deleted.');
    }

    // Add dropdown option
    public function optionStore(Request $request, Client $client, AssetAuditField $field)
    {
        abort_if($field->client_id !== $client->id, 403);
        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
        ]);
        AuditFieldOption::create([
            'field_id' => $field->id,
            'value'    => $data['value'],
            'order_index' => (int) ($field->options()->max('order_index') + 1),
        ]);
        return back()->with('ok', 'Option added.');
    }

    // Delete dropdown option
    public function optionDestroy(Client $client, AssetAuditField $field, AuditFieldOption $option)
    {
        abort_if($field->client_id !== $client->id || $option->field_id !== $field->id, 403);
        $option->delete();
        return back()->with('ok', 'Option deleted.');
    }

    /* ======================
     *  UPLOADS (CSV append)
     * ====================== */

    // List uploads + upload form (ignores first row)
    public function uploads(Client $client)
    {
        if (Auth::guard('web')->check()) {
            $files = AssetAuditFile::where('client_id', $client->id)
                ->where('type', 'csv')
                ->orderByDesc('created_at')
                ->paginate(20);

            $auditors = Auditor::where('status', 'active')->orderBy('name')->get(['id', 'name']);
            // we need fields order to map CSV columns (after the first header line)
            $fields = AssetAuditField::where('client_id', $client->id)->orderBy('order_index')->get();
        } else {
            $files = AssetAuditFile::where('client_id', $client->id)
                ->where('type', 'csv')
                ->orderByDesc('created_at')
                ->paginate(20);

            $auditors = Auditor::where('status', 'active')->orderBy('name')->get(['id', 'name']);
            // we need fields order to map CSV columns (after the first header line)
            $fields = AssetAuditField::where('client_id', $client->id)->orderBy('order_index')->get();
        }


        return view('admin.pages.asset_audit.uploads', compact('client', 'files', 'auditors', 'fields'));
    }

    // Upload CSV (append rows, ignore header)
    public function uploadStore(Request $request, Client $client)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            // OPTIONAL: let the UI post a specific register_id if you don’t want “latest”
            // 'register_id' => ['nullable','exists:asset_registers,id'],
        ]);

        // 1) Load the headings set to validate against (latest by default)
        $register = AssetRegister::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$register) {
            return back()->withErrors('No Asset Register headings found for this client. Upload headings first.');
        }

        $savedHeadings = is_array($register->headings)
            ? $register->headings
            : json_decode($register->headings, true);

        if (empty($savedHeadings) || !is_array($savedHeadings)) {
            return back()->withErrors('Saved register headings are invalid for this client.');
        }

        // 2) Require an audit schema and prepare a label->fieldName map
        $fields = AssetAuditField::where('client_id', $client->id)
            ->orderBy('order_index')
            ->get(['name', 'label']);

        if ($fields->isEmpty()) {
            return back()->withErrors('Define Audit Schema (fields) before uploading Audit CSV.');
        }

        // Map normalized label -> schema key (field name)
        $norm = fn($v) => strtoupper(trim((string)$v));
        $labelToKey = [];
        foreach ($fields as $f) {
            $labelToKey[$norm($f->label)] = $f->name;
        }

        // 3) Open file & read header
        $file     = $request->file('file');
        $original = $file->getClientOriginalName();
        $stream   = fopen($file->getRealPath(), 'r');
        if ($stream === false) return back()->withErrors('Could not read uploaded file.');

        // Read first row (header) as-is
        $csvHeader = $this->readCsvFirstRow($stream); // use your helper
        if (empty($csvHeader)) {
            fclose($stream);
            return back()->withErrors('CSV appears empty or has no header row.');
        }

        // 4) Validate header: same length & same headings (order must match)
        $savedNorm = array_map($norm, $savedHeadings);
        $currNorm  = array_map($norm, $csvHeader);

        if (count($savedNorm) !== count($currNorm)) {
            fclose($stream);
            return back()->withErrors('CSV header column count does not match saved Asset Register headings for this client.');
        }

        for ($i = 0; $i < count($savedNorm); $i++) {
            if ($savedNorm[$i] !== $currNorm[$i]) {
                fclose($stream);
                return back()->withErrors("CSV header mismatch at column " . ($i + 1) . ": expected '{$savedHeadings[$i]}', found '{$csvHeader[$i]}'.");
            }
        }

        // 5) Ensure every heading label maps to a schema field key
        //    (This requires that your schema labels match register headings; if not, use the "Import to Schema" shortcut.)
        $headerFieldKeys = [];
        foreach ($csvHeader as $label) {
            $key = $labelToKey[$norm($label)] ?? null;
            // if (!$key) {
            //     fclose($stream);
            //     return back()->withErrors("Audit Schema is missing a field with label '{$label}'. Create it (or use Import to Schema) and retry.");
            // }
            $headerFieldKeys[] = $key; // schema key in same order as CSV columns
        }

        // 6) Prepare audit file record
        $fileRec = AssetAuditFile::create([
            'client_id'       => $client->id,
            'auditor_id'      => Auth::guard('auditor')->id(),
            'type'            => 'csv',
            'label'           => pathinfo($original, PATHINFO_FILENAME),
            'source_filename' => $original,
            'rows_count'      => 0,
        ]);

        // 7) Read data rows (ignore the first line we already consumed)
        $rowsImported = 0;
        while (($row = fgetcsv($stream, 0, ',')) !== false) {

            // Detect and recover if the whole row is a single semicolon-joined cell
            if (count($row) === 1 && str_contains($row[0] ?? '', ';')) {
                $row = str_getcsv($row[0], ';');
            }

            // Skip empty lines
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;

            // Column count mismatch (bad line) — skip row safely
            if (count($row) < count($headerFieldKeys)) {
                // Optionally collect a warning; for now just skip
                continue;
            }

            // Map values by header -> schema key
            $payload = [];
            foreach ($headerFieldKeys as $i => $schemaKey) {
                $payload[$schemaKey] = isset($row[$i]) ? trim((string)$row[$i]) : null;
            }

            AssetAuditRow::create([
                'client_id'  => $client->id,
                'file_id'    => $fileRec->id,
                'auditor_id' => Auth::guard('auditor')->id(),
                'data'       => $payload,
            ]);
            $rowsImported++;
        }
        fclose($stream);

        $fileRec->update(['rows_count' => $rowsImported]);

        return back()->with('ok', "Uploaded {$original} — {$rowsImported} rows appended (header matched saved register headings).");
    }

    // Delete an uploaded file (and its rows)
    public function uploadDestroy(Client $client, AssetAuditFile $file)
    {
        abort_if($file->client_id !== $client->id || $file->type !== 'csv', 403);
        $file->rows()->delete();
        $file->delete();
        return back()->with('ok', 'Upload deleted.');
    }

    private function skipCsvHeader($stream): void
    {
        // Skip UTF-8 BOM and first row
        $bom = fread($stream, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($stream);
        fgetcsv($stream, 0, ','); // discard first row
    }

    /* ===========================
     *  AUDITOR MANUAL CAPTURE UI
     * =========================== */

    // Show capture form for a client
    public function capture(Client $client)
    {
        $fields = AssetAuditField::where('client_id', $client->id)->orderBy('order_index')->with('options')->get();

        // “today’s” manual day file for this auditor, or create
        $auditorId = Auth::id() ?? null; // adjust if your auditor auth differs
        $dayLabel  = 'Manual ' . date('Y-m-d');
        $file = AssetAuditFile::firstOrCreate(
            [
                'client_id'  => $client->id,
                'auditor_id' => $auditorId,
                'type'       => 'manual_day',
                'label'      => $dayLabel,
            ],
            ['rows_count' => 0]
        );

        return view('admin.pages.asset_audit.capture', compact('client', 'fields', 'file'));
    }

    // Save one row (AJAX)
    public function saveRow(Request $request, Client $client)
    {
        $fields = AssetAuditField::where('client_id', $client->id)->orderBy('order_index')->get();
        if ($fields->isEmpty()) return response()->json(['ok' => false, 'msg' => 'No schema'], 422);

        $payload = [];
        $rules   = [];
        foreach ($fields as $f) {
            $key = $f->name;
            // Required if checkbox false still passes as nullable, but we check required
            $r = [];
            if ($f->required && $f->type !== 'checkbox') $r[] = 'required';
            // Basic typing
            if ($f->type === 'number') $r[] = 'numeric';
            if ($f->type === 'date')   $r[] = 'date';
            $rules[$key] = $r;
            $payload[$key] = $request->input($key, $f->type === 'checkbox' ? (bool)$request->boolean($key) : null);
        }
        $request->validate($rules);

        $auditorId = Auth::id() ?? null; // adjust if needed
        $file = AssetAuditFile::firstOrCreate(
            [
                'client_id'  => $client->id,
                'auditor_id' => $auditorId,
                'type'       => 'manual_day',
                'label'      => 'Manual ' . date('Y-m-d'),
            ],
            ['rows_count' => 0]
        );

        AssetAuditRow::create([
            'client_id'  => $client->id,
            'file_id'    => $file->id,
            'auditor_id' => $auditorId,
            'data'       => $payload,
        ]);

        $file->increment('rows_count');

        return response()->json(['ok' => true]);
    }

    // Auditor’s own rows
    public function myRows(Client $client)
    {
        $auditorId = Auth::id() ?? null;
        $rows = AssetAuditRow::where('client_id', $client->id)
            ->where('auditor_id', $auditorId)
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('admin.pages.asset_audit.my_rows', compact('client', 'rows'));
    }

    // Finish day -> simply create a new manual_day label next save (or you can keep as is)
    public function finishDay(Client $client)
    {
        // This endpoint exists to conceptually “close” the day; practically we just allow a new file on next save
        return back()->with('ok', 'Day finished. A new manual day file will start on the next save.');
    }

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
