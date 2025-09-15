@extends('admin.dashboard')
@section('title', 'Capture')
@php
    $isAdmin = auth('web')->check();
    $ns = $isAdmin ? 'admin.' : 'auditor.';
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-3">
            <i class="bi bi-filetype-csv me-2"></i>
            Manual Audit — {{ $client->company_name }}
        </h4>
        <a href="{{ route($ns . 'client.index', $client->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left-short me-1"></i> Back
        </a>
    </div>

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
                        @foreach ($fields as $f)
                            <div class="{{ $f->type === 'textarea' ? 'col-12' : 'col-md-6' }}">
                                <label class="form-label">
                                    {{ $f->label }}
                                    @if ($f->required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($f->type === 'dropdown')
                                    <div class="d-flex gap-2">
                                        <select class="form-select" name="{{ $f->name }}"
                                            {{ $f->required ? 'required' : '' }}>
                                            <option value="">-- SELECT --</option>
                                            @foreach ($f->options as $opt)
                                                <option value="{{ $opt->value }}">{{ $opt->value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($f->scan_enabled)
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                data-scan="{{ $f->name }}">Scan</button>
                                        @endif
                                    </div>
                                @elseif($f->type === 'textarea')
                                    <textarea class="form-control" rows="2" name="{{ $f->name }}" {{ $f->required ? 'required' : '' }}></textarea>
                                @elseif($f->type === 'date')
                                    <input type="date" class="form-control" name="{{ $f->name }}"
                                        {{ $f->required ? 'required' : '' }}>
                                @elseif($f->type === 'number')
                                    <input type="number" class="form-control" name="{{ $f->name }}"
                                        {{ $f->required ? 'required' : '' }}>
                                @elseif($f->type === 'checkbox')
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="{{ $f->name }}"
                                            value="1">
                                        <label class="form-check-label small text-muted">{{ $f->description }}</label>
                                    </div>
                                @else
                                    <div class="d-flex gap-2">
                                        <input class="form-control" name="{{ $f->name }}"
                                            placeholder="{{ $f->description }}" {{ $f->required ? 'required' : '' }}>
                                        @if ($f->scan_enabled)
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                data-scan="{{ $f->name }}">Scan</button>
                                        @endif
                                    </div>
                                @endif

                                @if ($f->description && $f->type !== 'checkbox')
                                    <div class="form-text">{{ $f->description }}</div>
                                @endif
                            </div>
                        @endforeach

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" id="btnNext">Save & Next</button>
                                <a href="{{ route($ns . 'audit.myRows', $client->id) }}"
                                    class="btn btn-outline-secondary">My
                                    Rows</a>
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
                    <video id="video" style="width:100%; max-height:60vh; background:#000;" playsinline autoplay
                        muted></video>
                    <div class="text-muted small mt-2"><span id="scanStatus">Ready.</span> — Point the camera at the code.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            function ready(fn) {
                document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn);
            }
            ready(function() {

                /* ----------------- Save form ----------------- */
                const form = document.getElementById('captureForm');
                const btn = document.getElementById('btnNext');
                form?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                    try {
                        const resp = await fetch(
                            "{{ route($ns . 'audit.saveRow', $client->id) }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]')
                                        .value
                                },
                                body: new FormData(form)
                            });
                        const json = await resp.json();
                        if (json.ok) {
                            form.querySelectorAll('input[type=text], input[type=number], textarea')
                                .forEach(i => i.value = '');
                            form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                            form.querySelectorAll('input[type=checkbox]').forEach(c => c.checked =
                                false);
                        } else {
                            alert(json.msg || 'Failed to save');
                        }
                    } catch (err) {
                        alert(err.message);
                    }
                    btn.disabled = false;
                    btn.textContent = 'Save & Next';
                });

                /* ----------------- Utilities ----------------- */
                const scanModalEl = document.getElementById('scanModal');
                const videoElem = document.getElementById('video');
                const statusEl = document.getElementById('scanStatus');
                let bsModal = null;

                let targetField = null,
                    stream = null,
                    rafId = null,
                    running = false;
                let codeReader = null,
                    readerControls = null;

                const secureOk = window.isSecureContext || ['localhost', '127.0.0.1', '::1'].includes(location
                    .hostname);
                const setStatus = (t) => {
                    if (statusEl) statusEl.textContent = t;
                };

                function showModal() {
                    if (window.bootstrap?.Modal) {
                        if (!bsModal) bsModal = new bootstrap.Modal(scanModalEl);
                        bsModal.show();
                    } else {
                        scanModalEl.classList.add('show');
                        scanModalEl.style.display = 'block';
                    }
                }

                function hideModal() {
                    if (window.bootstrap?.Modal && bsModal) bsModal.hide();
                    else {
                        scanModalEl.classList.remove('show');
                        scanModalEl.style.display = 'none';
                    }
                }

                function stopScanner() {
                    running = false;
                    try {
                        readerControls?.stop?.();
                    } catch {}
                    try {
                        codeReader?.reset?.();
                    } catch {}
                    try {
                        stream?.getTracks?.().forEach(t => t.stop());
                    } catch {}
                    if (rafId) {
                        try {
                            cancelAnimationFrame(rafId);
                        } catch {}
                        rafId = null;
                    }
                    stream = null;
                    videoElem.srcObject = null;
                    setStatus('Ready.');
                }
                async function getStream() {
                    try {
                        return await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: {
                                    ideal: 'environment'
                                },
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                }
                            },
                            audio: false
                        });
                    } catch {
                        return await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        });
                    }
                }

                function pasteIntoTarget(text) {
                    const t = String(text || '').trim();
                    if (!t || !targetField) return;

                    // Safely build the selector for fields whose names may contain spaces, () etc.
                    const sel = '#captureForm [name="' + targetField.replace(/"/g, '\\"') + '"]';
                    const input = document.querySelector(sel);
                    if (!input) return;

                    if (input.tagName === 'SELECT') {
                        const opt = Array.from(input.options).find(o => o.value === t);
                        if (opt) {
                            input.value = t;
                        } else {
                            input.add(new Option(t, t, true, true));
                        }
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } else if (input.type === 'checkbox') {
                        input.checked = !!t;
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } else {
                        input.value = t;
                        input.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                }


                /* ----------- Dynamic script loader ----------- */
                function loadScript(src) {
                    return new Promise((res, rej) => {
                        const s = document.createElement('script');
                        s.src = src;
                        s.async = true;
                        s.onload = () => res(true);
                        s.onerror = () => rej(new Error('load fail ' + src));
                        document.head.appendChild(s);
                    });
                }
                async function tryLoadAny(urls) {
                    for (const u of urls) {
                        try {
                            await loadScript(u);
                            return true;
                        } catch {}
                    }
                    return false;
                }
                async function ensureZXing() {
                    if (window.ZXingBrowser && window.ZXing) return true;
                    // Try jsDelivr first, then unpkg
                    const okLib = await tryLoadAny([
                        'https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js',
                        'https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js'
                    ]);
                    const okBrw = await tryLoadAny([
                        'https://cdn.jsdelivr.net/npm/@zxing/browser@0.1.7/umd/index.min.js',
                        'https://unpkg.com/@zxing/browser@0.1.7/umd/index.min.js'
                    ]);
                    return (okLib && okBrw && window.ZXingBrowser);
                }
                async function ensureJsQR() {
                    if (window.jsQR) return true;
                    return await tryLoadAny([
                        'https://unpkg.com/jsqr@1.4.0/dist/jsQR.js',
                        'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js'
                    ]);
                }

                /* ----------------- Scanners ------------------ */
                async function scanWithDetector() {
                    if (!('BarcodeDetector' in window)) throw new Error('no-detector');
                    const detector = new window.BarcodeDetector({
                        formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'itf',
                            'codabar', 'pdf417', 'aztec', 'data_matrix', 'upc_a', 'upc_e'
                        ]
                    });
                    running = true;
                    const tick = async () => {
                        if (!running) return;
                        try {
                            const codes = await detector.detect(videoElem);
                            if (codes && codes.length) {
                                const val = String(codes[0].rawValue || '');
                                if (val) {
                                    pasteIntoTarget(val);
                                    stopScanner();
                                    hideModal();
                                    return;
                                }
                            }
                        } catch {}
                        rafId = requestAnimationFrame(tick);
                    };
                    tick();
                }

                async function scanWithZXing() {
                    const ok = await ensureZXing();
                    if (!ok) {
                        throw new Error('zxing-missing');
                    }
                    const {
                        BrowserMultiFormatReader
                    } = window.ZXingBrowser;
                    if (!codeReader) codeReader = new BrowserMultiFormatReader();
                    const onDecode = (result, err, controls) => {
                        if (result) {
                            const text = (typeof result.getText === 'function') ? result.getText() :
                                result.text;
                            if (text) {
                                pasteIntoTarget(text);
                                try {
                                    controls?.stop?.();
                                } catch {}
                                stopScanner();
                                hideModal();
                            }
                        }
                    };
                    readerControls = await codeReader.decodeFromVideoElement(videoElem, onDecode);
                }

                async function scanWithJsQR() {
                    const ok = await ensureJsQR();
                    if (!ok) {
                        alert('Camera OK, but QR decoder missing (jsQR blocked).');
                        stopScanner();
                        return;
                    }
                    running = true;
                    await new Promise(r => {
                        if (videoElem.readyState >= 2) r();
                        else videoElem.onloadeddata = () => r();
                    });
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d', {
                        willReadFrequently: true
                    });
                    const sample = () => {
                        if (!running) return;
                        const vw = videoElem.videoWidth || 640;
                        const vh = videoElem.videoHeight || 480;
                        const maxSide = 1024;
                        const scale = Math.min(1, maxSide / Math.max(vw, vh));
                        canvas.width = Math.floor(vw * scale);
                        canvas.height = Math.floor(vh * scale);
                        ctx.drawImage(videoElem, 0, 0, canvas.width, canvas.height);
                        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        try {
                            const res = window.jsQR(img.data, img.width, img.height, {
                                inversionAttempts: 'attemptBoth'
                            });
                            if (res && res.data) {
                                pasteIntoTarget(res.data);
                                stopScanner();
                                hideModal();
                                return;
                            }
                        } catch (e) {
                            console.error('jsQR error', e);
                            running = false;
                            stopScanner();
                            return;
                        }
                        rafId = requestAnimationFrame(sample);
                    };
                    sample();
                }

                async function startScanner() {
                    if (!secureOk) {
                        alert('Use HTTPS or http://localhost / http://127.0.0.1');
                        return;
                    }
                    setStatus('Opening camera...');
                    try {
                        stream = await getStream();
                    } catch (e) {
                        console.error(e);
                        alert('Could not open camera. Allow permission and close Zoom/Teams/Meet.');
                        setStatus('Camera error');
                        return;
                    }
                    videoElem.srcObject = stream;
                    try {
                        await videoElem.play();
                    } catch {}
                    setStatus('Scanning...');

                    // 1) Try native
                    try {
                        await scanWithDetector();
                        return;
                    } catch {
                        /* continue */
                    }

                    // 2) Try ZXing via multiple CDNs
                    try {
                        await scanWithZXing();
                        return;
                    } catch (e) {
                        if (String(e.message).includes('zxing-missing')) {
                            alert(
                                'ZXing library not loaded. If a network/ad blocker is on, allow cdn.jsdelivr.net or unpkg.com.'
                            );
                        }
                    }

                    // 3) Final fallback: jsQR from CDN
                    try {
                        await scanWithJsQR();
                        return;
                    } catch {
                        alert('QR fallback (jsQR) failed to load. Please allow CDN domains.');
                        stopScanner();
                    }
                }

                /* ------------- Event delegation ------------- */
                document.addEventListener('click', (ev) => {
                    const btn = ev.target.closest('button[data-scan]');
                    if (!btn) return;
                    ev.preventDefault();
                    targetField = btn.getAttribute('data-scan');
                    showModal();
                    setTimeout(startScanner, 200);
                });

                scanModalEl?.addEventListener('hidden.bs.modal', stopScanner);
                document.addEventListener('click', (ev) => {
                    if (ev.target.closest('[data-bs-dismiss="modal"]')) stopScanner();
                });

            });
        })();
    </script>
@endpush
