@extends('admin.dashboard')
@section('title', 'Choose Client — Reports')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i> Choose Client for Reports</h4>
        <a href="{{ route('admin.client.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back to Clients
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('admin.reports.index', 0) }}"
                onsubmit="event.preventDefault(); const id=document.getElementById('client_id').value; if(id){ window.location = '{{ url('/admin/clients') }}/'+id+'/reports'; }">
                <div class="col-lg-6">
                    <label class="form-label">Client</label>
                    <select id="client_id" class="form-select" required>
                        <option value="">— Select Client —</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pick a client to open the Report Builder.</div>
                </div>
                <div class="col-lg-6 d-flex align-items-end">
                    <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i> Continue</button>
                </div>
            </form>
        </div>
    </div>
@endsection
