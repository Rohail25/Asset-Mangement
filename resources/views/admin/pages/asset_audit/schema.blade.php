@extends('admin.dashboard')
@section('title', 'Audit Schema')

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
            Audit Schema — {{ $client->company_name }}
        </h4>
        <a href="{{ route($ns . 'client.index', $client->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back
        </a>
    </div>



    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white"><strong>Add Field</strong></div>
                <div class="card-body">
                    <form class="row g-2" method="POST" action="{{ route($ns . 'audit.field.store', $client->id) }}">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Key (no spaces)</label>
                            <input name="name" class="form-control" placeholder="serial_number" required
                                pattern="[A-Za-z0-9_]+">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label</label>
                            <input name="label" class="form-control" placeholder="Serial Number" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                @foreach (['text', 'number', 'date', 'textarea', 'dropdown', 'checkbox'] as $t)
                                    <option value="{{ $t }}">{{ strtoupper($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="required" value="1"
                                    id="req">
                                <label class="form-check-label" for="req">Required</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="scan_enabled" value="1"
                                    id="scan">
                                <label class="form-check-label" for="scan">Scan Enabled</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input name="order_index" type="number" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Add Field</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white"><strong>Fields</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Key</th>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Req</th>
                                    <th>Scan</th>
                                    <th>Options</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fields as $f)
                                    <tr>
                                        <td>{{ $f->order_index }}</td>
                                        <td><code>{{ $f->name }}</code></td>
                                        <td>{{ $f->label }}</td>
                                        <td>{{ strtoupper($f->type) }}</td>
                                        <td>{{ $f->required ? 'YES' : 'NO' }}</td>
                                        <td>{{ $f->scan_enabled ? 'YES' : 'NO' }}</td>
                                        <td style="min-width:220px">
                                            @if ($f->type === 'dropdown')
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    @foreach ($f->options as $o)
                                                        <span class="badge text-bg-secondary">
                                                            {{ $o->value }}
                                                            <form class="d-inline" method="POST"
                                                                action="{{ route($ns . 'audit.option.destroy', [$client->id, $f->id, $o->id]) }}">
                                                                @csrf @method('DELETE')
                                                                <button class="btn btn-link btn-sm text-white p-0 ms-1"
                                                                    onclick="return confirm('Delete option?')">×</button>
                                                            </form>
                                                        </span>
                                                    @endforeach
                                                </div>
                                                <form class="d-flex gap-2" method="POST"
                                                    action="{{ route($ns . 'audit.option.store', [$client->id, $f->id]) }}">
                                                    @csrf
                                                    <input class="form-control form-control-sm" name="value"
                                                        placeholder="ADD OPTION" required>
                                                    <button class="btn btn-outline-primary btn-sm">Add</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route($ns . 'audit.field.destroy', [$client->id, $f->id]) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Delete field?')">Del</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No fields defined yet.</td>
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
