@extends('admin.dashboard')
@section('title', 'Live Audit')

@section('content')
    <h4 class="mb-2">Live Audit — {{ $client->company_name }}</h4>
    <div class="mb-3 small text-muted">Totals update as auditors capture. (Refresh page or add polling if you want live
        refresh.)</div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="display-6">{{ $totalToday }}</div>
                    <div class="text-muted">Rows captured today</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Per Auditor (today)</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Auditor</th>
                                    <th>Count</th>
                                    <th>Avg sec/asset</th>
                                    <th>First</th>
                                    <th>Last</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perAuditor as $r)
                                    <tr>
                                        <td>{{ $r['auditor'] }}</td>
                                        <td>{{ $r['count'] }}</td>
                                        <td>{{ $r['avg_sec'] }}</td>
                                        <td>{{ $r['first'] ?? '—' }}</td>
                                        <td>{{ $r['last'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No activity today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white"><strong>Recent Rows (edit)</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Auditor</th>
                                    <th>Time</th>
                                    <th>Data</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRows as $r)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="small">{{ $r->auditor?->name ?? '—' }}</td>
                                        <td class="small text-muted">{{ $r->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <pre class="mb-0 small">{{ json_encode($r->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-outline-primary btn-sm"
                                                href="{{ route('admin.reports.row.edit', [$client->id, $r->id]) }}">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No rows.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
