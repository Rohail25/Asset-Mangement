@extends('admin.dashboard')
@section('title', 'Report Results')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Report Results</h4>
        <a href="{{ route('admin.reports.index', $client->id) }}" class="btn btn-outline-secondary btn-sm">← Builder</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><strong>Total Register Rows:</strong> {{ $totalReg }}</div>
                <div class="col-md-3"><strong>Total Audit Rows:</strong> {{ $totalAudit }}</div>
                <div class="col-md-3"><strong>Total Matched:</strong> {{ $totalMatched }}</div>
                <div class="col-md-3">
                    <form method="POST" action="{{ route('admin.reports.export', $client->id) }}">
                        @csrf
                        <input type="hidden" name="results_json" value='{!! $resultsJson !!}'>
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-download me-1"></i> Export CSV
                        </button>
                    </form>

                </div>
            </div>

            <hr>
            <div class="row g-3">
                @foreach ($stages as $label => $count)
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <div class="small text-muted">{{ $label }}</div>
                            <div class="fs-5 fw-semibold">{{ $count }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Preview (first 200)</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Match Type</th>
                            @foreach ($regCols as $c)
                                <th>reg: {{ $keyToLabel[$c] ?? $c }}</th>
                            @endforeach
                            @foreach ($audCols as $c)
                                <th>audit: {{ $keyToLabel[$c] ?? $c }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preview as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p['match_type'] }}</td>
                                @foreach ($regCols as $c)
                                    <td class="small">{{ $p['register'][$c] ?? '' }}</td>
                                @endforeach
                                @foreach ($audCols as $c)
                                    <td class="small">{{ $p['audit'][$c] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 1 + count($regCols) + count($audCols) }}" class="text-center text-muted">No
                                    matches.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
