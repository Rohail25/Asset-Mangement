@extends('admin.dashboard')
@section('title','Auditors')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Auditors</h4>
  <a href="{{ route('admin.auditor.create') }}" class="btn btn-primary btn-sm">Create Auditor</a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($auditors as $a)
          <tr>
            <td class="fw-semibold">{{ $a->name }}</td>
            <td>{{ $a->email }}</td>
            <td>{{ $a->username }}</td>
            <td>
              <span class="badge {{ $a->status==='active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                {{ strtoupper($a->status) }}
              </span>
            </td>
            <td class="small text-muted">{{ $a->created_at->format('d M Y H:i') }}</td>
            <td class="text-end">
              <div class="btn-group">
                {{-- @if($a->status==='inactive')
                  <form method="POST" action="{{ route('admin.auditors.activate',$a) }}">@csrf @method('PATCH')
                    <button class="btn btn-outline-success btn-sm">Activate</button>
                  </form>
                @else
                  <form method="POST" action="{{ route('admin.auditors.deactivate',$a) }}">@csrf @method('PATCH')
                    <button class="btn btn-outline-secondary btn-sm">Deactivate</button>
                  </form>
                @endif --}}
                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.auditor.edit', $a->id) }}">Edit</a>
                <form class="d-inline" method="POST" action="{{ route('admin.auditor.destroy',$a->id) }}">
                  @csrf @method('GET')
                  <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete auditor?')">Del</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">No auditors yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{-- <div class="mt-2">{{ $auditors->links() }}</div> --}}
  </div>
</div>
@endsection
