@extends('admin.dashboard')
@section('title', 'Report Builder — beta')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Report Builder — beta</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.live', $client->id) }}" class="btn btn-outline-secondary btn-sm">Live View</a>
            <a href="{{ route('admin.client.index') }}" class="btn btn-outline-secondary btn-sm">← Clients</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.reports.run', $client->id) }}" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label">Register file (CSV)</label>
                    <select name="register_file_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach ($csvFiles as $f)
                            <option value="{{ $f->id }}">
                                {{ $f->source_filename ?? ($f->label ?? 'CSV') }} — {{ $f->created_at->format('d M Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Pick the CSV that represents your register data.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Register field (schema key)</label>
                    <select name="register_field" class="form-select" required>
                        @foreach ($fields as $f)
                            <option value="{{ $f->name }}">{{ $f->label }} ({{ $f->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Audit field (schema key)</label>
                    <select name="audit_field" class="form-select" required>
                        @foreach ($fields as $f)
                            <option value="{{ $f->name }}">{{ $f->label }} ({{ $f->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Audit file scope</label>
                    <select name="file_scope" class="form-select">
                        <option value="all">All</option>
                        <option value="csv_only">CSV only</option>
                        <option value="manual_only">Manual only</option>
                    </select>
                    <div class="form-text">Or choose a specific audit file below.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Specific audit file (optional)</label>
                    <select name="audit_file_id" class="form-select">
                        <option value="">— Any —</option>
                        @foreach ($allFiles as $f)
                            <option value="{{ $f->id }}">
                                [{{ strtoupper($f->type) }}] {{ $f->label ?? ($f->source_filename ?? '—') }}
                                — {{ $f->created_at->format('d M Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="use_exact" value="0">
                        <input class="form-check-input" type="checkbox" id="use_exact" name="use_exact" value="1"
                            checked>
                        <label class="form-check-label" for="use_exact">Exact (ignore spaces) — 1.1</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="use_pre" value="0">
                        <input class="form-check-input" type="checkbox" id="use_pre" name="use_pre" value="1">
                        <label class="form-check-label" for="use_pre">Pre-Match</label>
                    </div>
                    <input type="number" min="1" max="255" class="form-control mt-1" name="pre_len"
                        placeholder="First X chars">
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="use_post" value="0">
                        <input class="form-check-input" type="checkbox" id="use_post" name="use_post" value="1">
                        <label class="form-check-label" for="use_post">Post-Match</label>
                    </div>
                    <input type="number" min="1" max="255" class="form-control mt-1" name="post_len"
                        placeholder="Last X chars">
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="use_zero_o" value="0">
                        <input class="form-check-input" type="checkbox" id="use_zero_o" name="use_zero_o"
                            value="1">
                        <label class="form-check-label" for="use_zero_o">0 ↔ O ignore</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-play-fill me-1"></i>Run Match</button>
                    <a href="{{ route('admin.reports.live', $client->id) }}" class="btn btn-outline-secondary">Live
                        View</a>
                </div>
            </form>
        </div>
    </div>
@endsection
