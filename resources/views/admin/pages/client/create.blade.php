@extends('admin.dashboard')
@section('title', 'Create Client')
@php
    $isAdmin = auth('web')->check();
    $isAuditor = auth('auditor')->check();
    $ns = $isAdmin ? 'admin.' : 'auditor.';
@endphp
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-building-add me-2"></i>Create Client</h4>
        <a href="{{ route('admin.client.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.client.store') }}">
                @csrf

                {{-- role fixed to client --}}
                <input type="hidden" name="role" value="client">

                <div class="col-md-6">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact (Phone)</label>
                    <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror"
                        value="{{ old('contact') }}">
                    @error('contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name"
                        class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}"
                        required>
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assign Auditor</label>
                    <select name="auditor_id" class="form-select @error('auditor_id') is-invalid @enderror" required>
                        <option value="">-- Select Auditor --</option>
                        @foreach ($auditors as $a)
                            <option value="{{ $a->id }}" {{ old('auditor_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->name }}</option>
                        @endforeach
                    </select>
                    @error('auditor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Audit Start Date</label>
                    <input type="date" name="audit_start_date"
                        class="form-control @error('audit_start_date') is-invalid @enderror"
                        value="{{ old('audit_start_date') }}">
                    @error('audit_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                        value="{{ old('due_date') }}">
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Audit Status</label>
                    <select name="audit_status" class="form-select @error('audit_status') is-invalid @enderror" required>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" {{ old('audit_status', 'Planning') === $s ? 'selected' : '' }}>
                                {{ $s }}</option>
                        @endforeach
                    </select>
                    @error('audit_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>



                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Save Client</button>
                    <a href="{{ route('admin.client.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
