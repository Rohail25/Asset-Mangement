@extends('admin.dashboard')
@section('title', 'Audit Uploads')

@section('content')
    <h4 class="mb-3">Audit Uploads — {{ $client->company_name }}</h4>
    @php
        $isAdmin = auth('web')->check();
        $isAuditor = auth('auditor')->check();
        $ns = $isAdmin ? 'admin.' : 'auditor.';
    @endphp
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Upload Audit CSV</strong></div>
                <div class="card-body">
                    <form class="row g-2" method="POST" action="{{ route($ns.'audit.upload.store', $client->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">CSV File</label>
                            <input type="file" name="file" accept=".csv,text/csv"
                                class="form-control @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">First line is ignored (schema already defined). Columns map by field
                                order.</div>
                        </div>
                        {{-- <div class="col-12">
                            <label class="form-label">Auditor (optional)</label>
                            <select name="auditor_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach ($auditors as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="col-12">
                            <button class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                    @if ($fields->isEmpty())
                        <div class="alert alert-warning mt-3">No schema yet. Create fields first.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Uploaded Files</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Label / File</th>
                                    <th>By</th>
                                    <th>Rows</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $f)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $f->label }}</div>
                                            <div class="small text-muted">{{ $f->source_filename }}</div>
                                        </td>
                                        <td class="small">{{ $f->auditor?->name ?? '—' }}</td>
                                        <td>{{ $f->rows_count }}</td>
                                        <td class="small text-muted">{{ $f->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route($ns.'audit.upload.destroy', [$client->id, $f->id]) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Delete this file & its rows?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No audit files yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- <div class="mt-2">{{ $files->links() }}</div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
