@extends('admin.dashboard')
@section('title','Edit Auditor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i>Edit Auditor</h4>
  <a href="{{ route('admin.auditor.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left-short me-1"></i> Back
  </a>
</div>

<div class="row g-3">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-body">
        <form class="row g-3" method="POST" action="{{ route('admin.auditor.update', $auditor->id) }}">
          @csrf
          @method('PUT')

          {{-- ROLE stays fixed to "auditor" --}}
          <input type="hidden" name="role" value="auditor">

          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input
              type="text"
              name="name"
              class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', $auditor->name) }}"
              placeholder="e.g., John Doe"
              required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input
              type="email"
              name="email"
              class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email', $auditor->email) }}"
              placeholder="name@company.com"
              required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input
              type="text"
              name="username"
              class="form-control @error('username') is-invalid @enderror"
              value="{{ old('username', $auditor->username) }}"
              placeholder="e.g., johndoe"
              required>
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">New Password <span class="text-muted small">(leave blank to keep)</span></label>
            <div class="input-group">
              <input
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                id="pw"
                placeholder="Min 8 chars">
              <button class="btn btn-outline-secondary" type="button" id="togglePw">
                <i class="bi bi-eye"></i>
              </button>
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Confirm Password</label>
            <input
              type="password"
              name="password_confirmation"
              class="form-control"
              id="pw2">
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select
              name="status"
              class="form-select @error('status') is-invalid @enderror"
              required>
              <option value="active"   {{ old('status', $auditor->status)==='active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $auditor->status)==='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary">
              <i class="bi bi-save2 me-1"></i> Update Auditor
            </button>
            <a href="{{ route('admin.auditor.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Side help card --}}
  <div class="col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start">
          <i class="bi bi-info-circle me-2 fs-5 text-primary"></i>
          <div>
            <div class="fw-semibold">Notes</div>
            <ul class="small mb-0 mt-1">
              <li>Leave the password fields blank to keep the current password.</li>
              <li>Status “inactive” can be used to prevent access without deleting the account.</li>
              <li>Role is fixed to <code>auditor</code> for security.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // show/hide password
  document.getElementById('togglePw')?.addEventListener('click', function(){
    const input = document.getElementById('pw');
    const icon = this.querySelector('i');
    if(input.type === 'password'){ input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
  });
</script>
@endpush
