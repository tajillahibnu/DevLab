<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Suara — Transkripsi percakapan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
    <style>
        :root{color-scheme:light;--paper:#f5f4ef;--white:#fffefa;--ink:#202820;--muted:#74796f;--line:#e5e4dc;--green:#29583e;--mint:#e8efe7;--coral:#b94e3c}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:15px 'DM Sans',sans-serif;min-height:100vh}.shell{max-width:1120px;margin:auto;padding:0 38px}.topbar{height:78px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:11px;font-weight:700;letter-spacing:-.04em;font-size:20px}.brand-mark{width:32px;height:32px;background:var(--green);border-radius:10px;display:grid;place-items:center;color:white}.brand-mark svg{width:18px}.top-note{font:11px 'DM Mono',monospace;color:var(--muted);letter-spacing:.05em}.hero{padding:68px 0 36px;max-width:730px}.eyebrow{font:11px 'DM Mono',monospace;text-transform:uppercase;letter-spacing:.12em;color:var(--green);display:flex;align-items:center;gap:9px}.eyebrow:before{content:'';width:20px;height:1px;background:var(--green)}h1{font:400 clamp(42px,6vw,68px)/.99 Newsreader,serif;letter-spacing:-.045em;margin:22px 0 18px;max-width:670px}h1 em{color:var(--green);font-weight:400}.hero p{color:var(--muted);line-height:1.7;margin:0;max-width:500px}.workspace{display:grid;grid-template-columns:1.12fr .88fr;gap:18px;align-items:stretch;padding-bottom:68px}.panel{background:var(--white);border:1px solid var(--line);border-radius:16px;overflow:hidden}.panel-head{padding:21px 23px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}.panel-title{font-weight:600;font-size:14px}.step{font:10px 'DM Mono',monospace;color:var(--muted);letter-spacing:.04em}.panel-body{padding:24px}.tabs{display:flex;background:#f2f1eb;padding:4px;border-radius:9px;gap:4px;margin-bottom:23px}.tab{height:38px;flex:1;border:0;border-radius:6px;color:var(--muted);font:500 13px 'DM Sans',sans-serif;cursor:pointer}.tab.active{background:var(--white);color:var(--ink);box-shadow:0 1px 3px #20282012}.recordbox{min-height:225px;background:#faf9f5;border:1px dashed #d5d6cc;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px;transition:.2s}.recordbox.recording{border-color:#d88979;background:#fff8f5}.record-icon{width:68px;height:68px;border-radius:50%;display:grid;place-items:center;background:#edf0e9;color:var(--green);margin-bottom:14px}.recording .record-icon{background:#f7e5df;color:var(--coral);animation:pulse 1.4s infinite}.record-icon svg{width:24px}.recordbox strong{font-size:14px}.recordbox p{font-size:12px;color:var(--muted);margin:6px 0 0}.timer{display:none;font:12px 'DM Mono',monospace;color:var(--coral);margin-top:11px}.recording .timer{display:block}.actions{display:flex;justify-content:center;gap:9px;margin-top:17px}.btn{min-height:42px;padding:0 16px;border-radius:7px;border:1px solid var(--line);background:var(--white);font:600 13px 'DM Sans',sans-serif;cursor:pointer;transition:background .15s,border .15s}.btn:hover{background:#f3f3ee}.btn:focus-visible,.tab:focus-visible{outline:3px solid #6d9b7b;outline-offset:2px}.btn.primary{background:var(--green);border-color:var(--green);color:white}.btn.primary:hover{background:#1f4831}.btn[disabled]{opacity:.5;cursor:not-allowed}.dropzone{min-height:225px;border:1px dashed #d5d6cc;border-radius:12px;background:#faf9f5;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:22px;cursor:pointer}.dropzone:hover{border-color:#93a695;background:#f8faf6}.upload-icon{width:44px;height:44px;border-radius:12px;background:var(--mint);display:grid;place-items:center;color:var(--green);margin-bottom:13px}.upload-icon svg{width:20px}.dropzone strong{font-size:14px}.dropzone span{font-size:12px;color:var(--muted);margin-top:6px}.file-name{font:11px 'DM Mono',monospace;color:var(--green);margin-top:12px;word-break:break-all}.submit-row{margin-top:16px;display:flex;justify-content:space-between;align-items:center}.format-note{color:var(--muted);font-size:11px}.result-empty{height:100%;min-height:366px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:36px}.result-symbol{width:58px;height:58px;border:1px solid var(--line);border-radius:18px;display:grid;place-items:center;color:#78907a;margin-bottom:17px;background:#fcfbf7}.result-symbol svg{width:23px}.result-empty strong{font:400 23px Newsreader,serif}.result-empty p{color:var(--muted);font-size:12px;line-height:1.7;max-width:230px;margin:9px 0 0}.result-content{padding:23px}.result-meta{display:flex;justify-content:space-between;align-items:center;padding-bottom:16px;border-bottom:1px solid var(--line);gap:10px}.result-meta span{font:10px 'DM Mono',monospace;text-transform:uppercase;letter-spacing:.07em;color:var(--muted)}.result-meta button{font:11px 'DM Sans',sans-serif;border:0;color:var(--green);cursor:pointer;background:transparent;padding:8px}.transcript{white-space:pre-wrap;line-height:1.85;font-size:14px;margin:18px 0 0;max-height:320px;overflow:auto}.alert{padding:12px 14px;background:#fbefec;border:1px solid #efd4ce;color:#773a30;border-radius:8px;font-size:12px;margin-bottom:16px}.footer{border-top:1px solid var(--line);padding:18px 0 30px;display:flex;justify-content:space-between;color:var(--muted);font:10px 'DM Mono',monospace}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}@keyframes pulse{50%{box-shadow:0 0 0 9px #b94e3c15}}@media(max-width:760px){.shell{padding:0 20px}.topbar{height:66px}.hero{padding:49px 0 28px}.workspace{grid-template-columns:1fr;gap:14px}.result-empty{min-height:265px}.top-note{font-size:9px}}@media(max-width:420px){.shell{padding:0 15px}.panel-body{padding:17px}.panel-head{padding:17px}.format-note{max-width:150px;line-height:1.5}.footer{font-size:9px;gap:12px}.footer span:last-child{text-align:right}}
    </style>
</head>
<body>
<div class="shell">
    <header class="topbar" aria-label="Navigasi utama">
        <a class="brand" href="/" aria-label="Suara, beranda"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 10v4M8 6v12M12 3v18M16 7v10M20 10v4"/></svg></span>suara</a>
        <span class="top-note">RUANG KERJA TRANSKRIPSI &nbsp;·&nbsp; BAHASA INDONESIA</span>
    </header>
    <main>
        <section class="hero">
            <div class="eyebrow">Dari suara menjadi cerita</div>
            <h1>Setiap percakapan,<br><em>tertangkap dengan jelas.</em></h1>
            <p>Rekam langsung atau unggah audio. AI akan mengubah percakapan Anda menjadi transkrip yang siap dibaca.</p>
        </section>
        <section class="workspace" aria-label="Transkripsi audio">
            <div class="panel">
                <div class="panel-head"><span class="panel-title">Sumber audio</span><span class="step">LANGKAH 01 / 02</span></div>
                <div class="panel-body">
                    @if ($errors->has('audio')) <div class="alert" role="alert">{{ $errors->first('audio') }}</div> @endif
                    <div class="tabs" role="tablist" aria-label="Pilih sumber audio">
                        <button class="tab active" type="button" role="tab" aria-selected="true" data-mode="record">Rekam suara</button>
                        <button class="tab" type="button" role="tab" aria-selected="false" data-mode="upload">Unggah file</button>
                    </div>
                    <div id="record-pane" role="tabpanel">
                        <div class="recordbox" id="recordbox">
                            <div class="record-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="3" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3m-4 0h8"/></svg></div>
                            <strong id="record-title">Mulai percakapan Anda</strong>
                            <p id="record-description">Pastikan mikrofon perangkat Anda aktif</p>
                            <span class="timer" id="timer">00:00</span>
                        </div>
                        <div class="actions"><button class="btn primary" id="record-button" type="button">Mulai merekam</button><button class="btn" id="stop-button" type="button" hidden>Hentikan</button></div>
                    </div>
                    <form id="upload-form" action="{{ route('transcribe') }}" method="POST" enctype="multipart/form-data" hidden>
                        @csrf
                        <label class="dropzone" for="audio-file"><span class="upload-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m-4 4 4-4 4 4M5 14v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5"/></svg></span><strong>Pilih rekaman untuk ditranskripsikan</strong><span>atau jatuhkan file audio di sini</span><span class="file-name" id="file-name">WAV, MP3, M4A, WEBM · Maks. 100 MB</span></label>
                        <input class="sr-only" id="audio-file" name="audio" type="file" accept="audio/*,.mp4,.mpeg,.mpga" required>
                        <div class="submit-row"><span class="format-note">Audio diproses dengan aman dan tidak disimpan.</span><button class="btn primary" type="submit" id="upload-submit" disabled>Transkripsikan</button></div>
                    </form>
                </div>
            </div>
            <div class="panel">
                <div class="panel-head"><span class="panel-title">Hasil transkrip</span><span class="step">LANGKAH 02 / 02</span></div>
                @if (session('transcript'))
                    <div class="result-content"><div class="result-meta"><span>{{ session('audio_name', 'Audio') }}</span><button type="button" id="copy-button">Salin transkrip</button></div><div class="transcript" id="transcript-text">{{ session('transcript') }}</div></div>
                @else
                    <div class="result-empty" id="result-empty"><span class="result-symbol"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v6h5M9 14h6m-6 4h6"/></svg></span><strong>Transkrip Anda akan muncul di sini</strong><p>Setelah audio selesai diproses, hasil percakapan tampil rapi di ruang ini.</p></div>
                @endif
            </div>
        </section>
    </main>
    <footer class="footer"><span>SUARA · TRANSKRIPSI CERDAS</span><span>Audio Anda diproses dengan aman</span></footer>
</div>
<script>
    const tabs = document.querySelectorAll('.tab');
    const recordPane = document.getElementById('record-pane');
    const uploadForm = document.getElementById('upload-form');
    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(item => { item.classList.toggle('active', item === tab); item.setAttribute('aria-selected', item === tab ? 'true' : 'false'); });
        const recording = tab.dataset.mode === 'record';
        recordPane.hidden = !recording;
        uploadForm.hidden = recording;
    }));

    let recorder;
    let chunks = [];
    let stream;
    let startedAt = 0;
    let timerInterval;
    const recordButton = document.getElementById('record-button');
    const stopButton = document.getElementById('stop-button');
    const recordbox = document.getElementById('recordbox');
    const formatTime = seconds => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;

    recordButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const preferred = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4'].find(type => MediaRecorder.isTypeSupported(type));
            recorder = new MediaRecorder(stream, preferred ? { mimeType: preferred } : undefined);
            chunks = [];
            recorder.ondataavailable = event => { if (event.data.size) chunks.push(event.data); };
            recorder.onstop = () => {
                stream.getTracks().forEach(track => track.stop());
                const blob = new Blob(chunks, { type: recorder.mimeType || 'audio/webm' });
                const ext = blob.type.includes('mp4') ? 'm4a' : 'webm';
                const file = new File([blob], `rekaman-${new Date().toISOString().slice(0, 10)}.${ext}`, { type: blob.type });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                document.getElementById('audio-file').files = transfer.files;
                document.getElementById('file-name').textContent = `${file.name} · ${(file.size / 1024).toFixed(1)} KB`;
                document.getElementById('upload-submit').disabled = false;

                tabs.forEach(tab => {
                    const active = tab.dataset.mode === 'upload';
                    tab.classList.toggle('active', active);
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                recordPane.hidden = true;
                uploadForm.hidden = false;
                document.getElementById('record-title').textContent = 'Rekaman siap';
                document.getElementById('record-description').textContent = 'Audio Anda siap dikirim untuk ditranskripsikan';
            };
            recorder.start(); startedAt = Date.now();
            recordbox.classList.add('recording');
            document.getElementById('record-title').textContent = 'Sedang merekam percakapan';
            document.getElementById('record-description').textContent = 'Tetap di halaman ini hingga rekaman selesai';
            recordButton.hidden = true; stopButton.hidden = false;
            timerInterval = setInterval(() => { document.getElementById('timer').textContent = formatTime(Math.floor((Date.now() - startedAt) / 1000)); }, 500);
        } catch (error) {
            alert('Mikrofon tidak dapat diakses. Izinkan akses mikrofon di browser, atau gunakan tab Unggah file.');
        }
    });
    stopButton.addEventListener('click', () => {
        clearInterval(timerInterval); recorder.stop();
        recordbox.classList.remove('recording'); recordButton.hidden = false; stopButton.hidden = true;
    });

    const input = document.getElementById('audio-file');
    input.addEventListener('change', () => {
        const file = input.files[0];
        document.getElementById('file-name').textContent = file
            ? `${file.name} · ${(file.size / 1048576).toFixed(1)} MB`
            : 'MP3, WAV, M4A, WEBM · Maks. 100 MB';
        document.getElementById('upload-submit').disabled = !file;
    });
    uploadForm.addEventListener('submit', () => {
        const button = document.getElementById('upload-submit');
        button.disabled = true; button.textContent = 'Mentranskripsikan (mohon tunggu)…';
    });
    document.getElementById('copy-button')?.addEventListener('click', async event => {
        await navigator.clipboard.writeText(document.getElementById('transcript-text').innerText);
        event.currentTarget.textContent = 'Tersalin';
        setTimeout(() => event.currentTarget.textContent = 'Salin transkrip', 1800);
    });
</script>
</body>
</html>
