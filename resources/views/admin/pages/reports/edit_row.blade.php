@extends('admin.dashboard')
@section('title', 'Edit Row')

@section('content')
    <h4 class="mb-3">Edit Row #{{ $row->id }} — {{ $client->company_name }}</h4>

    <div class="card">
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.reports.row.update', [$client->id, $row->id]) }}">
                @csrf
                @foreach ($fields as $f)
                    <div class="{{ $f->type === 'textarea' ? 'col-12' : 'col-md-6' }}">
                        <label class="form-label">{{ $f->label }} ({{ $f->name }})</label>

                        @if ($f->type === 'textarea')
                            <textarea class="form-control" rows="2" name="{{ $f->name }}">{{ $row->data[$f->name] ?? '' }}</textarea>
                        @elseif($f->type === 'checkbox')
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="{{ $f->name }}" value="1"
                                    {{ !empty($row->data[$f->name]) ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted">{{ $f->description }}</label>
                            </div>
                        @else
                            <input class="form-control" name="{{ $f->name }}"
                                value="{{ $row->data[$f->name] ?? '' }}">
                        @endif
                    </div>
                @endforeach

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.reports.live', $client->id) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection
