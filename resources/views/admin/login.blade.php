<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login • Asset Audit</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --aa-primary: #0d6efd;
      --aa-deep: #0b1e5b;
      --aa-grad-1: #0d6efd;
      --aa-grad-2: #5f9bff;
      --aa-grad-3: #0b1e5b;
    }
    body{
      min-height:100vh;
      display:grid;
      place-items:center;
      background:
        radial-gradient(1200px 600px at -10% -10%, rgba(13,110,253,.20), transparent 60%),
        radial-gradient(1000px 500px at 110% 110%, rgba(11,30,91,.20), transparent 60%),
        linear-gradient(180deg, #f6f9ff 0%, #eef3ff 100%);
    }
    .login-wrap{
      width:100%;
      max-width: 980px;
      padding: 20px;
    }
    .card-login{
      border: 1px solid rgba(13,110,253,.08);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 18px 50px rgba(13,110,253,.08);
      background: rgba(255,255,255,.85);
      backdrop-filter: blur(10px);
    }
    .login-left{
      background: radial-gradient(120% 120% at 0% 0%, var(--aa-grad-1) 0%, var(--aa-grad-2) 50%, var(--aa-grad-3) 100%);
      color: #eaf2ff;
      position: relative;
      padding: 42px 36px;
    }
    .orb{
      position:absolute; inset:auto auto -50px -50px;
      width:160px; height:160px; border-radius:50%;
      background: radial-gradient(circle at 30% 30%, #fff, rgba(255,255,255,.4) 40%, transparent 60%);
      opacity:.3; filter: blur(2px);
    }
    .brand-badge{
      display:inline-grid; place-items:center;
      width:60px; height:60px; border-radius:18px;
      background: #fff; color: var(--aa-deep);
      font-weight:900; font-size:20px;
      box-shadow: 0 10px 30px rgba(0,0,0,.15);
    }
    .tagline{
      margin-top:14px;
      color:#d8e6ff;
      font-size:.95rem;
    }
    .login-right{
      padding: 36px;
      background: #fff;
    }
    .btn-primary{
      box-shadow: 0 8px 24px rgba(13,110,253,.25);
    }
    .footer-note{
      font-size:.85rem; color:#6b7a90;
    }
  </style>
</head>
<body>

<div class="login-wrap">
  <div class="card card-login">
    <div class="row g-0">
      {{-- Left / Visual --}}
      <div class="col-lg-5 login-left d-flex flex-column justify-content-between">
        <div>
          <div class="brand-badge mb-3">AA</div>
          <h3 class="fw-bold mb-2">Asset Audit Admin</h3>
          <p class="tagline mb-4">
            Secure access to clients, auditors, registers and audits — all in one place.
          </p>

          <ul class="list-unstyled small mb-4">
            <li class="mb-2"><i class="bi bi-check-circle me-2"></i> Role-based access</li>
            <li class="mb-2"><i class="bi bi-check-circle me-2"></i> CSV register & audit imports</li>
            <li class="mb-2"><i class="bi bi-check-circle me-2"></i> Live capture with barcode/QR</li>
          </ul>
        </div>
        <div class="orb"></div>
        <div class="footer-note mt-4">
          <i class="bi bi-shield-lock me-1"></i> Encrypted & secure • © {{ date('Y') }}
        </div>
      </div>

      {{-- Right / Form --}}
      <div class="col-lg-7 login-right">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0 fw-bold">Sign in</h5>
          @if(session('ok'))
            <span class="badge text-bg-success">{{ session('ok') }}</span>
          @endif
        </div>

        @if($errors->any())
          <div class="alert alert-danger py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="row g-3">
          @csrf
          <div class="col-12">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="col-12">
            <label class="form-label d-flex justify-content-between">
              <span>Password</span>
              {{-- <a href="{{ route('password.request', [], false) }}" class="small text-decoration-none">Forgot?</a> --}}
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
              <input type="password" name="password" id="password"
                     class="form-control @error('password') is-invalid @enderror"
                     placeholder="••••••••" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePw">
                <i class="bi bi-eye"></i>
              </button>
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            {{-- <a href="{{ route('admin.register') }}" class="small text-decoration-none">
              Create admin
            </a> --}}
          </div>

          <div class="col-12 d-grid">
            <button class="btn btn-primary py-2">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
          </div>
        </form>

        <div class="text-center mt-3 small text-muted">
          Having trouble? Contact your system admin.
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Show/hide password
  document.getElementById('togglePw')?.addEventListener('click', function(){
    const input = document.getElementById('password');
    const icon  = this.querySelector('i');
    if(input.type === 'password'){ input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
  });
</script>
</body>
</html>
