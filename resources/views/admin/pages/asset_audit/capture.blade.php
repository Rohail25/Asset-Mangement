@extends('admin.dashboard')
@section('title','Capture')
@php
    $isAdmin = auth('web')->check();
    $isAuditor = auth('auditor')->check();
    $ns = $isAdmin ? 'admin.' : 'auditor.';
@endphp
@section('content')
<h4 class="mb-1">Manual Audit — {{ $client->company_name }}</h4>
<div class="text-muted small mb-3">
  Day File: <strong>{{ $file->label }}</strong> | Rows: {{ $file->rows_count }}
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white"><strong>New Asset</strong></div>
      <div class="card-body">
        <form id="captureForm" class="row g-2">
          @csrf
          @foreach($fields as $f)
            <div class="{{ $f->type==='textarea' ? 'col-12' : 'col-md-6' }}">
              <label class="form-label">{{ $f->label }} @if($f->required)<span class="text-danger">*</span>@endif</label>

              @if($f->type==='dropdown')
                <div class="d-flex gap-2">
                  <select class="form-select" name="{{ $f->name }}" {{ $f->required?'required':'' }}>
                    <option value="">-- SELECT --</option>
                    @foreach($f->options as $opt)
                      <option value="{{ $opt->value }}">{{ $opt->value }}</option>
                    @endforeach
                  </select>
                  @if($f->scan_enabled)
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-scan="{{ $f->name }}">Scan</button>
                  @endif
                </div>

              @elseif($f->type==='textarea')
                <textarea class="form-control" rows="2" name="{{ $f->name }}" {{ $f->required?'required':'' }}></textarea>

              @elseif($f->type==='date')
                <input type="date" class="form-control" name="{{ $f->name }}" {{ $f->required?'required':'' }}>

              @elseif($f->type==='number')
                <input type="number" class="form-control" name="{{ $f->name }}" {{ $f->required?'required':'' }}>

              @elseif($f->type==='checkbox')
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="{{ $f->name }}" value="1">
                  <label class="form-check-label small text-muted">{{ $f->description }}</label>
                </div>

              @else
                <div class="d-flex gap-2">
                  <input class="form-control" name="{{ $f->name }}" placeholder="{{ $f->description }}" {{ $f->required?'required':'' }}>
                  @if($f->scan_enabled)
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-scan="{{ $f->name }}">Scan</button>
                  @endif
                </div>
              @endif

              @if($f->description && $f->type!=='checkbox')
                <div class="form-text">{{ $f->description }}</div>
              @endif
            </div>
          @endforeach
 
          <div class="col-12">
            <div class="d-flex gap-2">
              <button class="btn btn-primary" id="btnNext">Save & Next</button>
              <a href="{{ route($ns.'audit.myRows',$client->id) }}" class="btn btn-outline-secondary">My Rows</a>
              <form method="POST" action="{{ route($ns.'audit.finishDay',$client->id) }}" onsubmit="return confirm('Finish day?')">
                @csrf
                <button class="btn btn-outline-danger">Finish Day</button>
              </form>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white"><strong>Quick Help</strong></div>
      <div class="card-body small">
        <ul>
          <li>First choose client; fields come from Admin’s schema.</li>
          <li>Use Scan to read a barcode/QR into the focused field.</li>
          <li>“My Rows” shows only what you captured.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

{{-- Scan Modal --}}
<div class="modal fade" id="scanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scan Barcode / QR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <video id="video" style="width: 100%; max-height: 60vh; background: #000;" playsinline></video>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- ZXing Browser CDN --}}
<script src="https://cdn.jsdelivr.net/npm/@zxing/browser@latest"></script>
<script>
  const form = document.getElementById('captureForm');
  const btn = document.getElementById('btnNext');

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    btn.disabled = true; btn.textContent = 'Saving...';
    try {
      const resp = await fetch("{{ route($ns.'audit.saveRow',$client->id) }}", {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value},
        body: new FormData(form)
      });
      const json = await resp.json();
      if(json.ok){
        form.querySelectorAll('input[type=text], input[type=number], textarea').forEach(i=> i.value = '');
        form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        form.querySelectorAll('input[type=checkbox]').forEach(c=> c.checked = false);
      } else { alert(json.msg || 'Failed to save'); }
    } catch(err){ alert(err.message); }
    btn.disabled = false; btn.textContent = 'Save & Next';
  });

  // Scan integration (ZXing)
  let targetField = null;
  let codeReader = null;
  const scanModal = new bootstrap.Modal(document.getElementById('scanModal'));

  document.querySelectorAll('[data-scan]').forEach(b=>{
    b.addEventListener('click', async ()=>{
      targetField = b.getAttribute('data-scan');
      scanModal.show();
      await startScanner();
    });
  });

  async function startScanner(){
    const videoElem = document.getElementById('video');
    if(!codeReader){
      codeReader = new ZXingBrowser.BrowserMultiFormatReader();
    }
    try {
      const devices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();
      const deviceId = devices?.[0]?.deviceId;
      const result = await codeReader.decodeOnceFromVideoDevice(deviceId, videoElem);
      // Fill and close
      if(result?.text && targetField){
        form.querySelector(`[name="${targetField}"]`).value = result.text;
      }
      await stopScanner();
      scanModal.hide();
    } catch(e){
      console.error(e);
      alert('Camera error or permission denied.');
      await stopScanner();
    }
  }
  async function stopScanner(){
    try{ await codeReader?.reset(); }catch(e){}
  }
  document.getElementById('scanModal').addEventListener('hidden.bs.modal', stopScanner);
</script>
@endpush
