@extends('admin.dashboard')
@section('title', 'My Rows')

{{-- check user role  --}}
@php
    $isAdmin = auth('web')->check();
    $isAuditor = auth('auditor')->check();
    $ns = $isAdmin ? 'admin.' : 'auditor.';
@endphp
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-3">
            <i class="bi bi-filetype-csv me-2"></i>
            My Rows — {{ $client->company_name }}
        </h4>
        <a href="{{ route($ns . 'client.index', $client->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as  $r)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <pre class="mb-0 small">{{ json_encode($r->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </td>
                                <td class="small text-muted">{{ $r->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No rows yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $rows->links() }}</div>
        </div>
    </div>
@endsection
