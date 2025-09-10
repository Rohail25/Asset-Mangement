@extends('admin.dashboard')
@section('title', 'Clients')
@php
    $isAdmin = auth('web')->check();
    $isAuditor = auth('auditor')->check();
    $ns = $isAdmin ? 'admin.' : 'auditor.';
@endphp
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-building me-2"></i>Clients</h4>
        @if ($ns == 'admin.')
            <a href="{{ route('admin.client.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Client
            </a>
        @endif

    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive h-100">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Company Name</th>
                            <th>Audit Status</th>
                            <th>Auditor</th>
                            <th>Audit Start Date</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $c)
                            <tr>
                                <td class="fw-semibold">{{ $c->name }}</td>
                                <td>{{ $c->email ?? '—' }}</td>
                                <td>{{ $c->contact ?? '—' }}</td>
                                <td>{{ $c->company_name }}</td>
                                <td><span class="badge text-bg-info">{{ $c->audit_status }}</span></td>
                                <td>{{ $c->auditor?->name ?? '—' }}</td>
                                <td class="small text-muted">
                                    {{ $c->audit_start_date ?? '—' }}
                                </td>
                                <td>{{ $c->due_date ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        @php
                                            $ns = Auth::guard('web')->check() ? 'admin.' : 'auditor.';
                                        @endphp
                                        {{-- Register (headings) quick link --}}
                                        <a class="btn btn-outline-secondary btn-sm"
                                            href="{{ route($ns . 'register.index', $c->id) }}">
                                            {{-- <i class="bi bi-filetype-csv me-1"></i> --}}
                                            Register
                                        </a>
                                        {{-- Audit actions dropdown --}}
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Audit
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route($ns . 'audit.schema', $c->id) }}">
                                                        <i class="bi bi-columns-gap me-1"></i> Schema
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route($ns . 'audit.uploads', $c->id) }}">
                                                        <i class="bi bi-upload me-1"></i> Uploads (CSV)
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route($ns . 'audit.capture', $c->id) }}">
                                                        <i class="bi bi-pencil-square me-1"></i> Manual Capture
                                                    </a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route($ns . 'audit.myRows', $c->id) }}">
                                                        <i class="bi bi-person-lines-fill me-1"></i> My Rows
                                                    </a></li>
                                            </ul>
                                        </div>
                                        {{-- Edit/Delete --}}
                                        @if ($ns == 'admin.')
                                            <a class="btn btn-outline-primary btn-sm"
                                                href="{{ route($ns . 'client.edit', $c->id) }}">Edit</a>
                                            <form method="POST" action="{{ route($ns . 'client.destroy', $c->id) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Delete this client?')">Del</button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $clients->links() }}</div>
        </div>
    </div>
@endsection
