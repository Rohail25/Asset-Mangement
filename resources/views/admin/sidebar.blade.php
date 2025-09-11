     {{-- SIDEBAR --}}
     <aside class="col-12 col-lg-3 col-xxl-2 px-0">
         <div class="sidebar-wrap">
             <div class="brand-badge">AA</div>
             <div class="sidebar-title">Admin Panel</div>
             {{-- check user role  --}}
             @php
                 $isAdmin = auth('web')->check();
                 $isAuditor = auth('auditor')->check();
                 $ns = $isAdmin ? 'admin.' : 'auditor.';
             @endphp
             <div class="sidebar-inner">
                 <div class="user-chip">
                     <div class="fw-semibold"><i class="bi bi-lightning-charge-fill me-1"></i> Quick Actions
                     </div>
                     <div class="small opacity-75">Navigate using the menus below</div>
                 </div>

                 {{-- SECTION: Management --}}
                 <div class="nav-section-title">Management</div>
                 <div class="aa-nav">

                     {{-- Clients dropdown --}}
                     <button class="btn btn-toggle" data-bs-toggle="collapse" data-bs-target="#menuClients"
                         aria-expanded="{{ request()->routeIs('admin.clients.*') ? 'true' : 'false' }}">
                         <i class="bi bi-building"></i>
                         <span>Clients</span>
                         <i class="bi bi-chevron-down ms-auto"></i>
                     </button>
                     <div class="collapse {{ request()->routeIs($ns . 'clients.*') ? 'show' : '' }}" id="menuClients">
                         <div class="aa-subnav">
                             <a href="{{ route($ns . 'client.index') }}"
                                 class="{{ request()->routeIs($ns . 'client.index') ? 'active' : '' }}">
                                 <i class="bi bi-list-ul"></i> List
                             </a>
                             @if ($ns == 'admin.')
                                 <a href="{{ route($ns . 'client.create') }}"
                                     class="{{ request()->routeIs($ns . 'client.create') ? 'active' : '' }}">
                                     <i class="bi bi-plus-circle"></i> Create
                                 </a>
                             @endif
                         </div>
                     </div>

                     {{-- Auditors dropdown --}}
                     @if ($ns == 'admin.')
                         <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuAuditors"
                             aria-expanded="{{ request()->routeIs('admin.auditors.*') ? 'true' : 'false' }}">
                             <i class="bi bi-people"></i>
                             <span>Auditors</span>
                             <i class="bi bi-chevron-down ms-auto"></i>
                         </button>
                         <div class="collapse {{ request()->routeIs('admin.auditors.*') ? 'show' : '' }}"
                             id="menuAuditors">
                             <div class="aa-subnav">
                                 <a href="{{ route('admin.auditor.index') }}"
                                     class="{{ request()->routeIs('admin.auditor.index') ? 'active' : '' }}">
                                     <i class="bi bi-list-ul"></i> List
                                 </a>
                                 <a href="{{ route('admin.auditor.create') }}"
                                     class="{{ request()->routeIs('admin.auditor.create') ? 'active' : '' }}">
                                     <i class="bi bi-plus-circle"></i> Create
                                 </a>
                             </div>
                         </div>
                     @endif


                     {{-- Assets dropdown (Register & Audit) – shown when $client exists --}}
                     {{-- @isset($client) --}}
                     {{-- <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuAssets"
                         aria-expanded="{{ request()->routeIs($ns . 'register.*') || request()->routeIs($ns . 'audit.*') ? 'true' : 'false' }}">
                         <i class="bi bi-hdd-network"></i>
                         <span>Assets</span>
                         <i class="bi bi-chevron-down ms-auto"></i>
                     </button>
                     <div class="collapse {{ request()->routeIs($ns . 'register.*') || request()->routeIs($ns . 'audit.*') ? 'show' : '' }}"
                         id="menuAssets">
                         <div class="aa-subnav">
                             <a href="{{ route('register.index') }}"
                                 class="{{ request()->routeIs('register.*') ? 'active' : '' }}">
                                 <i class="bi bi-filetype-csv"></i> Asset Register
                             </a>
                             <a href="{{ route('admin.audit.schema', $client) }}"
                                 class="{{ request()->routeIs('admin.audit.schema') ? 'active' : '' }}">
                                 <i class="bi bi-columns-gap"></i> Audit Schema
                             </a>
                             <a href="{{ route('admin.audit.uploads', $client) }}"
                                 class="{{ request()->routeIs('admin.audit.uploads') ? 'active' : '' }}">
                                 <i class="bi bi-upload"></i> Audit Uploads
                             </a>
                         </div>
                     </div> --}}
                     {{-- @endisset --}}

                     {{-- ASSET AUDIT (Admin) --}}
                     {{-- @isset($client) --}}
                     {{-- <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuAssets"
                                    aria-expanded="{{ request()->routeIs($ns.'audit.schema') || request()->routeIs($ns.'audit.uploads') ? 'true' : 'false' }}">
                                    <i class="bi bi-hdd-network"></i>
                                    <span>Asset Audit</span>
                                    <i class="bi bi-chevron-down ms-auto"></i>
                                </button>
                                <div class="collapse {{ request()->routeIs($ns.'audit.schema') || request()->routeIs($ns.'audit.uploads') ? 'show' : '' }}"
                                    id="menuAssets">
                                    <div class="aa-subnav"> --}}
                     {{-- Schema builder --}}
                     {{-- <a href="{{ route($ns.'audit.schema', $client->id) }}"
                                            class="{{ request()->routeIs($ns.'audit.schema') ? 'active' : '' }}">
                                            <i class="bi bi-columns-gap"></i> Schema
                                        </a> --}}

                     {{-- CSV uploads (ignore header + append) --}}
                     {{-- <a href="{{ route($ns.'audit.uploads', $client->id) }}"
                                            class="{{ request()->routeIs($ns.'audit.uploads') ? 'active' : '' }}">
                                            <i class="bi bi-upload"></i> Uploads
                                        </a>
                                    </div>
                                </div> --}}
                     {{-- @endisset --}}

                     {{-- AUDITOR / SELF ACTIONS (Auditor & Admin can use capture) --}}
                     {{-- @isset($client) --}}
                     <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuMyAudit"
                         aria-expanded="{{ request()->routeIs($ns . 'audit.capture') || request()->routeIs($ns . 'audit.myRows') ? 'true' : 'false' }}">
                         <i class="bi bi-qr-code-scan"></i>
                         <span>Manual Capture</span>
                         <i class="bi bi-chevron-down ms-auto"></i>
                     </button>
                     <div class="collapse {{ request()->routeIs($ns . 'audit.capture') || request()->routeIs($ns . 'audit.myRows') ? 'show' : '' }}"
                         id="menuMyAudit">
                         <div class="aa-subnav">
                             <a href="" class="{{ request()->routeIs($ns . 'audit.capture') ? 'active' : '' }}">
                                 <i class="bi bi-pencil-square"></i> Capture
                             </a>
                             <a href="" class="{{ request()->routeIs($ns . 'audit.myRows') ? 'active' : '' }}">
                                 <i class="bi bi-person-lines-fill"></i> My Rows
                             </a>
                         </div>
                     </div>
                     {{-- @endisset --}}

                     @php
                         $isAdmin = auth('web')->check();
                         $isAuditor = auth('auditor')->check();
                         $ns = $isAdmin ? 'admin.' : 'auditor.';
                     @endphp

                     {{-- Reports menu --}}
                     @if ($ns == 'admin.')


                         @if (isset($client))
                             <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuReports"
                                 aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
                                 <i class="bi bi-graph-up-arrow"></i>
                                 <span>Reports</span>
                                 <i class="bi bi-chevron-down ms-auto"></i>
                             </button>
                             <div class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}"
                                 id="menuReports">
                                 <div class="aa-subnav">
                                     <a href="{{ route('admin.reports.index', $client->id) }}"
                                         class="{{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                         <i class="bi bi-sliders"></i> Builder
                                     </a>
                                     <a href="{{ route('admin.reports.live', $client->id) }}"
                                         class="{{ request()->routeIs('admin.reports.live') ? 'active' : '' }}">
                                         <i class="bi bi-activity"></i> Live View
                                     </a>
                                 </div>
                             </div>
                         @else
                             <a class="btn btn-toggle mt-2" href="{{ route('admin.reports.chooseClient') }}">
                                 <i class="bi bi-graph-up-arrow"></i>
                                 <span>Reports</span>
                             </a>
                         @endif

                     @endif

                     {{-- Auditor (self) --}}
                     {{-- @can('isAuditorOrAdmin')
              <button class="btn btn-toggle mt-2" data-bs-toggle="collapse" data-bs-target="#menuMyAudit"
                aria-expanded="{{ request()->routeIs('auditor.*') ? 'true' : 'false' }}">
                <i class="bi bi-qr-code-scan"></i>
                <span>My Audit</span>
                <i class="bi bi-chevron-down ms-auto"></i>
              </button>
              <div class="collapse {{ request()->routeIs('auditor.*') ? 'show' : '' }}" id="menuMyAudit">
                <div class="aa-subnav">
                  <a href="{{ route('auditor.selectClient') }}" class="{{ request()->routeIs('auditor.selectClient') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i> Select Client
                  </a>
                </div>
              </div>
              @endcan --}}

                 </div>
             </div>

             <div class="mt-auto small opacity-75 pt-2">
                 <i class="bi bi-shield-lock me-1"></i> v1.0 • © {{ date('Y') }}
             </div>
         </div>
     </aside>
