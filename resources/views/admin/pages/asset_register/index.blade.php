@extends('admin.dashboard')
@section('title', 'Asset Register')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-filetype-csv me-2"></i>
            Asset Register
        </h4>
        @php
            $isAdmin = auth('web')->check();
            $isAuditor = auth('auditor')->check();
            $ns = $isAdmin ? 'admin.' : 'auditor.';
        @endphp
        <a href="{{ route($ns.'client.index', $client->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        {{-- Upload card --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Upload CSV (Register Header)</strong></div>
                <div class="card-body">
                    <form class="row g-3" method="POST" action="{{ route($ns.'register.store', $client->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">CSV File</label>
                            <input type="file" name="file" accept=".csv,text/csv"
                                class="form-control @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">The system will auto-detect the <strong>first row</strong> as headings
                                and save only those.</div>
                        </div>

                        {{-- <div class="col-12">
            <label class="form-label">Uploaded By (Auditor)</label>
            <select name="auditor_id" class="form-select @error('auditor_id') is-invalid @enderror" required>
              <option value="">— Select Auditor —</option>
              @foreach ($auditors as $a)
                <option value="{{ $a->id }}" {{ old('auditor_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>
              @endforeach
            </select>
            @error('auditor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Required to satisfy the <code>audit_id</code> FK of your migration.</div>
          </div> --}}

                        <div class="col-12">
                            <button class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i> Upload & Save Headings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Uploaded list --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Uploaded Heading Sets</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Source File</th>
                                    <th>Headings (preview)</th>
                                    <th>Auditor</th>
                                    <th>Uploaded</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $f)
                                    <tr>
                                        <td class="fw-medium">{{ $f->source_filename ?? '—' }}</td>
                                        <td style="max-width:420px">
                                            @php
                                                $headings = is_array($f->headings)
                                                    ? $f->headings
                                                    : json_decode($f->headings, true);
                                                $preview = collect($headings)->take(6)->implode(', ');
                                                $extra = max(count($headings) - 6, 0);

                                            @endphp
                                            <span class="small">{{ $preview }}@if ($extra > 0)
                                                    <span class="text-muted"> +{{ $extra }} more</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="small">{{ $f->auditor?->name ?? '—' }}</td>
                                        <td class="small text-muted">{{ $f->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route($ns.'register.destroy', [$client->id, $f->id]) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Delete this heading set?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No uploads yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2">
                            {{-- {{ $files->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
