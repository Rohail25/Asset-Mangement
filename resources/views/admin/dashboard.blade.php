<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Asset Audit')</title>

    {{-- Bootstrap CSS + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --aa-sidebar-w: 280px;
            --aa-primary: #0d6efd;
            --aa-primary-2: #5aa2ff;
            --aa-bg-dark: #0b1020;
        }

        body {
            min-height: 100vh;
            background: #f6f8fb;
        }

        /* NAVBAR */
        .aa-navbar {
            backdrop-filter: saturate(140%) blur(6px);
        }

        /* SIDEBAR */
        .sidebar-wrap {
            position: sticky;
            top: 0;
            min-height: 100dvh;
            width: var(--aa-sidebar-w);
            padding: 20px 18px;
            background: radial-gradient(120% 120% at 0% 0%, var(--aa-primary) 0%, #123a98 45%, #0b1e5b 100%);
            color: #e9f1ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            /* center content horizontally */
            text-align: center;
            /* center text */
        }

        .sidebar-inner {
            width: 100%;
            max-width: 230px;
            /* keep content tighter for a balanced look */
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-inline: auto;
        }

        .brand-badge {
            display: inline-grid;
            place-items: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #d1e2ff 100%);
            color: #0b1e5b;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .15);
            margin-inline: auto;
        }

        .sidebar-title {
            font-weight: 700;
            letter-spacing: .4px;
            margin-top: 6px;
        }

        .user-chip {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 14px;
            padding: 10px 12px;
            font-size: .875rem;
        }

        .nav-section-title {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            opacity: .8;
            text-transform: uppercase;
            margin: 10px 0 2px;
        }

        /* SIDEBAR LINKS */
        .aa-nav .btn-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            /* center text & chevron */
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            color: #eaf2ff;
            text-decoration: none;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .15);
            transition: all .2s ease;
        }

        .aa-nav .btn-toggle:hover {
            background: rgba(255, 255, 255, .08);
        }

        .aa-nav .btn-toggle[aria-expanded="true"] {
            background: linear-gradient(180deg, rgba(255, 255, 255, .16), rgba(255, 255, 255, .06));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
        }

        .aa-subnav {
            padding: 6px 0 8px;
            display: grid;
            gap: 8px;
        }

        .aa-subnav a {
            display: flex;
            align-items: center;
            justify-content: center;
            /* center sublinks */
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            color: #eaf2ff;
            text-decoration: none;
            font-size: .92rem;
            border: 1px dashed transparent;
            transition: all .2s ease;
        }

        .aa-subnav a:hover {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .18);
        }

        .aa-subnav a.active {
            background: #ffffff;
            color: #0b1e5b;
            font-weight: 700;
            border-color: transparent;
            box-shadow: 0 6px 18px rgba(13, 110, 253, .25);
        }

        /* MAIN CONTENT */
        .content {
            flex: 1 1 auto;
            padding: 24px;
        }

        .card {
            border: 1px solid #e9eef7;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
            border-radius: 14px;
        }

        /* RESPONSIVE */
        @media (max-width: 991.98px) {
            .sidebar-wrap {
                width: 100%;
                min-height: auto;
                position: static;
                border-radius: 0 0 18px 18px;
            }

            .sidebar-inner {
                max-width: 100%;
            }
        }
    </style>
    @stack('head')
</head>

<body>

    {{-- TOP NAV --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom aa-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="bi bi-shield-check me-1"></i> Asset Audit
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="topNav" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item small me-lg-2 text-muted">
                        <i class="bi bi-person-circle me-1"></i>
                        {{-- {{ auth()->user()->name ?? 'User' }} ({{ auth()->user()->role ?? 'role' }}) --}}
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}"> @csrf
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
       @include('admin.sidebar')

            {{-- MAIN CONTENT --}}
            <main class="content col-12 col-lg-9 col-xxl-10 p-0 pt-5 pe-2">
                @if (session('ok'))
                    <div class="alert alert-success">{{ session('ok') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    {{-- <script>
    // Auto-uppercase all text inputs/textareas (safety)
    document.addEventListener('input', (e) => {
      const t = e.target;
      if (t.matches('input[type="text"], textarea')) { t.value = t.value.toUpperCase(); }
    }, true);
  </script> --}}
</body>

</html>
