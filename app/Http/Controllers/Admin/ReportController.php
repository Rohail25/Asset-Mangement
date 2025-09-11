<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetAuditField;
use App\Models\AssetAuditFile;
use App\Models\AssetAuditRow;
use App\Models\AssetRegister;
use App\Models\AssetRegisterRow;
use App\Models\Auditor;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /* =======================================================
     * Report Builder (choose files/fields + matching options)
     * ======================================================= */
    public function index(Client $client)
    {
        // Schema fields (we’ll show labels, but submit the field "name"/key)
        $fields = AssetAuditField::where('client_id', $client->id)
            ->orderBy('order_index')
            ->get(['id', 'name', 'label']);

        // Files available
        $allFiles = AssetAuditFile::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get(['id', 'type', 'label', 'source_filename', 'rows_count', 'created_at']);

        $csvFiles    = $allFiles->where('type', 'csv')->values();
        $manualFiles = $allFiles->where('type', 'manual_day')->values();

        return view('admin.pages.reports.index', [
            'client'      => $client,
            'fields'      => $fields,
            'csvFiles'    => $csvFiles,    // candidate register files and audit files
            'manualFiles' => $manualFiles, // optional audit filter
            'allFiles'    => $allFiles,
        ]);
    }

    /* =======================
     * Run matching (preview)
     * ======================= */
    public function run(Request $request, Client $client)
    {
        $data = $request->validate([
            // REGISTER side (pick ONE file and ONE field to represent the "register")
            'register_file_id' => ['required', 'exists:asset_audit_files,id'],
            'register_field'   => ['required', 'string'],  // schema key (e.g., serial_number)

            // AUDIT side (select scope/file + column to compare against)
            'audit_field'    => ['required', 'string'],    // schema key
            'file_scope'     => ['nullable', 'in:all,csv_only,manual_only'],
            'audit_file_id'  => ['nullable', 'exists:asset_audit_files,id'],

            // strategies
            'use_exact'      => ['nullable', 'boolean'],
            'use_pre'        => ['nullable', 'boolean'],
            'use_post'       => ['nullable', 'boolean'],
            'use_zero_o'     => ['nullable', 'boolean'],
            'pre_len'        => ['nullable', 'integer', 'min:1', 'max:255'],
            'post_len'       => ['nullable', 'integer', 'min:1', 'max:255'],
        ]);

        // strategy toggles
        $useExact = $request->boolean('use_exact');
        $usePre   = $request->boolean('use_pre');
        $usePost  = $request->boolean('use_post');
        $useZO    = $request->boolean('use_zero_o');

        if (!$useExact && !$usePre && !$usePost && !$useZO) {
            $useExact = true; // default if user forgot to tick anything
        }

        $preLen  = (int)($request->input('pre_len')  ?: 0);
        $postLen = (int)($request->input('post_len') ?: 0);

        $regField = $data['register_field']; // schema key on register side
        $audField = $data['audit_field'];    // schema key on audit side

        // REGISTER rows = rows belonging to the selected register_file_id
        $registerRows = AssetAuditRow::where('client_id', $client->id)
            ->where('file_id', $data['register_file_id'])
            ->get(['id', 'data']);

        // AUDIT rows = may be a specific file, or all (optionally filtered by type)
        $audQuery = AssetAuditRow::where('client_id', $client->id);

        // If a specific audit file is chosen
        if (!empty($data['audit_file_id'])) {
            $audQuery->where('file_id', $data['audit_file_id']);
        } else {
            // Otherwise filter by scope (and avoid matching the same register file against itself)
            if (($data['file_scope'] ?? null) === 'csv_only') {
                $audQuery->whereHas('file', fn($q) => $q->where('type', 'csv'));
            } elseif (($data['file_scope'] ?? null) === 'manual_only') {
                $audQuery->whereHas('file', fn($q) => $q->where('type', 'manual_day'));
            }
        }

        // Optional: don’t allow matching register file to itself unless user explicitly picked it
        if (empty($data['audit_file_id'])) {
            $audQuery->where(function ($q) use ($data) {
                $q->whereNull('file_id')->orWhere('file_id', '!=', $data['register_file_id']);
            });
        }

        $auditRows = $audQuery->get(['id', 'data']);

        // Normalizers
        $norm = function ($v) { // uppercase + remove spaces
            return strtoupper(preg_replace('/\s+/', '', (string)$v));
        };
        $zeroOrO = function ($v) { // uppercase + remove separators + O=>0
            $v = strtoupper((string)$v);
            $v = str_replace([' ', '-', '_'], '', $v);
            return strtr($v, ['O' => '0']);
        };

        // Pools (id => raw string value)
        $unmatchedRegister = [];
        foreach ($registerRows as $r) {
            $val = $r->data[$regField] ?? null;
            $unmatchedRegister[$r->id] = $val === null ? '' : (string)$val;
        }

        $unmatchedAudit = [];
        foreach ($auditRows as $a) {
            $val = $a->data[$audField] ?? null;
            $unmatchedAudit[$a->id] = $val === null ? '' : (string)$val;
        }

        $results = []; // ['register_id','audit_id','type']
        $stages  = []; // label => matched count

        $takePair = function ($rid, $aid, $type) use (&$results, &$unmatchedRegister, &$unmatchedAudit) {
            $results[] = ['register_id' => $rid, 'audit_id' => $aid, 'type' => $type];
            unset($unmatchedRegister[$rid], $unmatchedAudit[$aid]);
        };

        $runStage = function (string $label, callable $keyFnReg, callable $keyFnAud)
        use (&$unmatchedRegister, &$unmatchedAudit, $takePair) {
            if (empty($unmatchedRegister) || empty($unmatchedAudit)) return 0;

            $index = [];
            foreach ($unmatchedAudit as $aid => $aval) {
                $k = $keyFnAud($aval);
                if ($k === '') continue;
                $index[$k][] = $aid;
            }

            $matched = 0;
            foreach ($unmatchedRegister as $rid => $rval) {
                $k = $keyFnReg($rval);
                if ($k === '' || empty($index[$k])) continue;
                $aid = array_shift($index[$k]); // one-to-one
                $takePair($rid, $aid, $label);
                $matched++;
            }
            return $matched;
        };

        if ($useExact) {
            $stages['1.1'] = $runStage('1.1', fn($v) => $norm($v), fn($v) => $norm($v));
        }
        if ($usePre && $preLen > 0) {
            $stages['PRE' . $preLen] = $runStage(
                'PRE' . $preLen,
                fn($v) => substr($norm($v), 0, $preLen),
                fn($v) => substr($norm($v), 0, $preLen)
            );
        }
        if ($usePost && $postLen > 0) {
            $stages['POST' . $postLen] = $runStage(
                'POST' . $postLen,
                fn($v) => substr($norm($v), -$postLen),
                fn($v) => substr($norm($v), -$postLen)
            );
        }
        if ($useZO) {
            $stages['0-O'] = $runStage('0-O', fn($v) => $zeroOrO($v), fn($v) => $zeroOrO($v));
        }

        // Summary & preview
        $totalReg     = count($unmatchedRegister) + array_sum($stages); // original register count
        $totalAudit   = count($unmatchedAudit)    + array_sum($stages); // original audit count
        $totalMatched = count($results);

        // Collect union of keys to display/export
        $regCols = $this->collectKeys($registerRows->pluck('data')->toArray());
        $audCols = $this->collectKeys($auditRows->pluck('data')->toArray());

        // Build preview (first 200)
        $preview = [];
        foreach (array_slice($results, 0, 200) as $row) {
            $r = $registerRows->firstWhere('id', $row['register_id']);
            $a = $auditRows->firstWhere('id', $row['audit_id']);
            $preview[] = [
                'match_type' => $row['type'],
                'register'   => $r?->data ?? [],
                'audit'      => $a?->data ?? [],
            ];
        }

        // Keep results for export (serialize to JSON in a hidden input)
        $resultsJson = json_encode($results);

        // For pretty CSV headers, map schema keys -> labels
        $fields = AssetAuditField::where('client_id', $client->id)->get(['name', 'label']);
        $keyToLabel = $fields->pluck('label', 'name')->toArray();
        // Summary counts using original dataset sizes
        $origReg   = $registerRows->count();
        $origAudit = $auditRows->count();
        $totalMatched = count($results);

        // NEW: percentages by stage (out of total register rows)
        $percentages = [];
        foreach ($stages as $label => $cnt) {
            $percentages[$label] = $origReg > 0 ? round(($cnt / $origReg) * 100, 2) : 0;
        }

        return view('admin.pages.reports.results', [
            'client'       => $client,
            'data'         => $data,
            'stages'       => $stages,
            'totalReg'     => $totalReg,
            'totalAudit'   => $totalAudit,
            'totalMatched' => $totalMatched,
            'regCols'      => $regCols,
            'audCols'      => $audCols,
            'preview'      => $preview,
            'resultsJson'  => $resultsJson,
            'keyToLabel'   => $keyToLabel,
            'percentages'  => $percentages,
        ]);
    }

    private function collectKeys(array $arr): array
    {
        $keys = [];
        foreach ($arr as $row) {
            foreach (array_keys((array)$row) as $k) {
                $keys[$k] = true;
            }
        }
        return array_values(array_keys($keys));
    }

    /* ==========================
     * Export CSV of all matches
     * ========================== */
    public function exportCsv(Request $request, Client $client)
    {
        $results = json_decode($request->input('results_json', '[]'), true);

        // Re-load all rows to reconstruct lines quickly
        $registerRowIds = array_column($results, 'register_id');
        $auditRowIds    = array_column($results, 'audit_id');

        $registerRows = AssetAuditRow::whereIn('id', $registerRowIds)->get(['id', 'data']);
        $auditRows    = AssetAuditRow::whereIn('id', $auditRowIds)->get(['id', 'data']);

        // Union of columns
        $regCols = $this->collectKeys($registerRows->pluck('data')->toArray());
        $audCols = $this->collectKeys($auditRows->pluck('data')->toArray());

        // Schema labels for pretty headers
        $fields = AssetAuditField::where('client_id', $client->id)->get(['name', 'label']);
        $keyToLabel = $fields->pluck('label', 'name')->toArray();

        $filename = 'matches_' . Str::slug($client->company_name) . '_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($results, $registerRows, $auditRows, $regCols, $audCols, $keyToLabel) {
            $out = fopen('php://output', 'w');

            // Header row (use labels where available)
            $header = ['match_type'];
            foreach ($regCols as $c) {
                $header[] = 'reg:'   . ($keyToLabel[$c] ?? $c);
            }
            foreach ($audCols as $c) {
                $header[] = 'audit:' . ($keyToLabel[$c] ?? $c);
            }
            fputcsv($out, $header);

            foreach ($results as $r) {
                $reg = optional($registerRows->firstWhere('id', $r['register_id']))->data ?? [];
                $aud = optional($auditRows->firstWhere('id', $r['audit_id']))->data ?? [];

                $line = [$r['type']];
                foreach ($regCols as $c) {
                    $line[] = $reg[$c] ?? '';
                }
                foreach ($audCols as $c) {
                    $line[] = $aud[$c] ?? '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /* ============= (optional) live progress ============= */
    public function live(Client $client)
    {
        $todayStart = Carbon::today();
        $todayEnd   = Carbon::tomorrow();

        $rowsToday = AssetAuditRow::where('client_id', $client->id)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get(['id', 'auditor_id', 'created_at']);

        $perAuditor = [];
        foreach ($rowsToday->groupBy('auditor_id') as $auditorId => $grp) {
            $count = $grp->count();
            $first = $grp->min('created_at');
            $last  = $grp->max('created_at');
            $seconds = max(1, $last->diffInSeconds($first));
            $avgPerAsset = $count > 0 ? round($seconds / $count, 1) : 0;
            $perAuditor[] = [
                'auditor' => \App\Models\Auditor::find($auditorId)?->name ?? '—',
                'count'   => $count,
                'avg_sec' => $avgPerAsset,
                'first'   => optional($first)->format('H:i'),
                'last'    => optional($last)->format('H:i'),
            ];
        }

        $totalToday = $rowsToday->count();

        $recentRows = AssetAuditRow::where('client_id', $client->id)
            ->orderByDesc('created_at')->limit(25)->get();

        return view('admin.pages.reports.live', compact('client', 'perAuditor', 'totalToday', 'recentRows'));
    }

    public function editRow(Client $client, AssetAuditRow $row)
    {
        abort_if($row->client_id !== $client->id, 403);
        $fields = AssetAuditField::where('client_id', $client->id)->orderBy('order_index')->get();
        return view('admin.pages.reports.edit_row', compact('client', 'row', 'fields'));
    }

    public function updateRow(Request $request, Client $client, AssetAuditRow $row)
    {
        abort_if($row->client_id !== $client->id, 403);
        $fields = AssetAuditField::where('client_id', $client->id)->get();

        $data = $row->data;
        foreach ($fields as $f) {
            $data[$f->name] = $f->type === 'checkbox'
                ? $request->boolean($f->name)
                : $request->input($f->name);
        }
        $row->update(['data' => $data]);

        return redirect()->route('admin.reports.live', $client->id)->with('ok', 'Row updated.');
    }
    public function chooseClient()
    {
        $clients = \App\Models\Client::orderBy('company_name')->get(['id', 'company_name', 'name']);
        return view('admin.pages.reports.choose_client', compact('clients'));
    }
}
