@extends('admin.dashboard')
@section('title','Create Auditor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Create Auditor</h4>
  <a href="{{ route('admin.auditor.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left-short me-1"></i> Back
  </a>
</div>

<div class="row g-3">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-body">
        <form class="row g-3" method="POST" action="{{ route('admin.auditor.store') }}">
          @csrf

          {{-- ROLE is fixed to "auditor" --}}
          <input type="hidden" name="role" value="auditor">

          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input
              type="text"
              name="name"
              class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name') }}"
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
              value="{{ old('email') }}"
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
              value="{{ old('username') }}"
              placeholder="e.g., johndoe"
              required>
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                id="pw"
                placeholder="Min 8 chars"
                required>
              <button class="btn btn-outline-secondary" type="button" id="togglePw">
                <i class="bi bi-eye"></i>
              </button>
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-text">Use a strong password (letters, numbers, symbols).</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Confirm Password</label>
            <input
              type="password"
              name="password_confirmation"
              class="form-control"
              id="pw2"
              required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select
              name="status"
              class="form-select @error('status') is-invalid @enderror"
              required>
              <option value="active"   {{ old('status','active')==='active'?'selected':'' }}>Active</option>
              <option value="inactive" {{ old('status')==='inactive'?'selected':'' }}>Inactive</option>
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i> Save Auditor
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
            <div class="fw-semibold">Tips</div>
            <ul class="small mb-0 mt-1">
              <li>Username & Email must be unique.</li>
              <li>Status “inactive” prevents the auditor from logging in (if you wire this to auth).</li>
              <li>Role is locked to <code>auditor</code> for security.</li>
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
