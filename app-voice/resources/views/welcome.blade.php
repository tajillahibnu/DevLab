<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Suara — Transkripsi Percakapan & Riwayat Audio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --paper: #f5f4ef;
            --white: #fffefa;
            --ink: #202820;
            --muted: #74796f;
            --line: #e5e4dc;
            --green: #29583e;
            --green-hover: #1f4831;
            --mint: #e8efe7;
            --coral: #b94e3c;
            --coral-soft: #fbefec;
            --coral-line: #efd4ce;
            --blue: #24577b;
            --blue-soft: #eaf2f8;
            --blue-line: #cde0ee;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font: 15px/1.5 'DM Sans', sans-serif;
            min-height: 100vh;
        }
        .shell {
            max-width: 1180px;
            margin: auto;
            padding: 0 32px 60px;
        }
        .topbar {
            height: 78px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-weight: 700;
            letter-spacing: -.04em;
            font-size: 21px;
            color: var(--ink);
            text-decoration: none;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            background: var(--green);
            border-radius: 10px;
            display: grid;
            place-items: center;
            color: white;
        }
        .brand-mark svg { width: 19px; height: 19px; }
        .top-note {
            font: 11px 'DM Mono', monospace;
            color: var(--muted);
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* Hero */
        .hero {
            padding: 56px 0 32px;
            max-width: 780px;
        }
        .eyebrow {
            font: 11px 'DM Mono', monospace;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--green);
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .eyebrow:before {
            content: '';
            width: 20px;
            height: 1px;
            background: var(--green);
        }
        h1 {
            font: 400 clamp(38px, 5.5vw, 62px)/1.02 Newsreader, serif;
            letter-spacing: -.04em;
            margin: 20px 0 16px;
        }
        h1 em {
            color: var(--green);
            font-style: italic;
        }
        .hero p {
            color: var(--muted);
            line-height: 1.7;
            margin: 0;
            font-size: 16px;
            max-width: 580px;
        }

        /* Flash Alerts */
        .alert {
            padding: 13px 18px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .alert-error {
            background: var(--coral-soft);
            border: 1px solid var(--coral-line);
            color: #773a30;
        }
        .alert-success {
            background: var(--mint);
            border: 1px solid #c9dec8;
            color: #1e452f;
        }

        /* Workspace Grid */
        .workspace {
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            gap: 22px;
            align-items: stretch;
            margin-bottom: 48px;
        }
        .panel {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .panel-head {
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fbfbf7;
        }
        .panel-title {
            font-weight: 600;
            font-size: 14px;
            letter-spacing: -.01em;
        }
        .step {
            font: 10px 'DM Mono', monospace;
            color: var(--muted);
            letter-spacing: .05em;
        }
        .panel-body {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: #f0efe8;
            padding: 4px;
            border-radius: 9px;
            gap: 4px;
            margin-bottom: 22px;
        }
        .tab {
            height: 38px;
            flex: 1;
            border: 0;
            border-radius: 6px;
            color: var(--muted);
            font: 500 13px 'DM Sans', sans-serif;
            cursor: pointer;
            background: transparent;
            transition: all .15s ease;
        }
        .tab.active {
            background: var(--white);
            color: var(--ink);
            box-shadow: 0 1px 3px rgba(32, 40, 32, 0.08);
            font-weight: 600;
        }

        /* Recording Box */
        .recordbox {
            min-height: 220px;
            background: #faf9f5;
            border: 1px dashed #d5d6cc;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 22px;
            transition: .2s;
        }
        .recordbox.recording {
            border-color: #d88979;
            background: #fff8f5;
        }
        .record-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #edf0e9;
            color: var(--green);
            margin-bottom: 14px;
        }
        .recording .record-icon {
            background: #f7e5df;
            color: var(--coral);
            animation: pulse 1.4s infinite;
        }
        .record-icon svg { width: 24px; height: 24px; }
        .recordbox strong { font-size: 14px; }
        .recordbox p { font-size: 12px; color: var(--muted); margin: 6px 0 0; }
        .timer {
            display: none;
            font: 13px 'DM Mono', monospace;
            color: var(--coral);
            margin-top: 10px;
            font-weight: 500;
        }
        .recording .timer { display: block; }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
        }

        /* Buttons */
        .btn {
            min-height: 40px;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--white);
            font: 600 13px 'DM Sans', sans-serif;
            cursor: pointer;
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: background .15s, border-color .15s, color .15s;
        }
        .btn:hover { background: #f3f3ee; }
        .btn:focus-visible, .tab:focus-visible {
            outline: 2px solid var(--green);
            outline-offset: 2px;
        }
        .btn.primary {
            background: var(--green);
            border-color: var(--green);
            color: white;
        }
        .btn.primary:hover { background: var(--green-hover); }
        .btn.danger {
            color: var(--coral);
            border-color: #f0cfc8;
            background: #fff8f6;
        }
        .btn.danger:hover {
            background: var(--coral-soft);
            border-color: var(--coral);
        }
        .btn.sm {
            min-height: 32px;
            padding: 0 11px;
            font-size: 12px;
            border-radius: 6px;
        }
        .btn[disabled] { opacity: .55; cursor: not-allowed; }

        /* Dropzone */
        .dropzone {
            min-height: 190px;
            border: 1px dashed #d5d6cc;
            border-radius: 12px;
            background: #faf9f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            cursor: pointer;
            transition: .2s;
        }
        .dropzone:hover, .dropzone.dragover {
            border-color: var(--green);
            background: #f7faf7;
        }
        .upload-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--mint);
            display: grid;
            place-items: center;
            color: var(--green);
            margin-bottom: 12px;
        }
        .upload-icon svg { width: 22px; height: 22px; }
        .dropzone strong { font-size: 14px; }
        .dropzone span { font-size: 12px; color: var(--muted); margin-top: 5px; }
        .file-name {
            font: 11px 'DM Mono', monospace;
            color: var(--green);
            margin-top: 10px;
            word-break: break-all;
            background: #edf3ed;
            padding: 3px 8px;
            border-radius: 5px;
        }

        /* Audio Preview Box */
        .preview-box {
            margin-top: 16px;
            padding: 14px 16px;
            background: #f7faf6;
            border: 1px solid #dbe6db;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--green);
            font-weight: 600;
        }
        .preview-box audio { width: 100%; height: 36px; border-radius: 6px; }

        .submit-row {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .format-note { color: var(--muted); font-size: 11px; }

        /* Result Panel */
        .result-empty {
            flex: 1;
            min-height: 360px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 36px 24px;
        }
        .result-symbol {
            width: 58px;
            height: 58px;
            border: 1px solid var(--line);
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: #78907a;
            margin-bottom: 17px;
            background: #fcfbf7;
        }
        .result-symbol svg { width: 24px; height: 24px; }
        .result-empty strong { font: 400 23px/1.2 Newsreader, serif; }
        .result-empty p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
            max-width: 270px;
            margin: 9px 0 0;
        }

        .result-content {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }
        .result-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
            gap: 12px;
            flex-wrap: wrap;
        }
        .meta-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .meta-title {
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .meta-details {
            font: 11px 'DM Mono', monospace;
            color: var(--muted);
        }
        .result-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Player in Result */
        .player-card {
            background: #faf9f4;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .player-card-label {
            font: 10px 'DM Mono', monospace;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .player-card audio {
            width: 100%;
            height: 38px;
            outline: none;
        }

        .transcript-box {
            background: #faf9f6;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 16px;
            flex: 1;
            min-height: 180px;
            max-height: 290px;
            overflow-y: auto;
        }
        .transcript-text {
            white-space: pre-wrap;
            line-height: 1.85;
            font-size: 14px;
            color: var(--ink);
            margin: 0;
            font-family: inherit;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 5px;
            font: 10px/1 'DM Mono', monospace;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 500;
        }
        .badge-record {
            background: var(--coral-soft);
            color: var(--coral);
            border: 1px solid var(--coral-line);
        }
        .badge-upload {
            background: var(--blue-soft);
            color: var(--blue);
            border: 1px solid var(--blue-line);
        }
        .badge svg { width: 11px; height: 11px; }

        /* History Section & Table */
        .history-section {
            margin-top: 16px;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }
        .history-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fbfbf7;
        }
        .history-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .history-title h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -.02em;
        }
        .history-count {
            font: 11px 'DM Mono', monospace;
            background: #eeebe1;
            padding: 3px 8px;
            border-radius: 12px;
            color: var(--ink);
        }
        .history-note {
            font: 11px 'DM Mono', monospace;
            color: var(--muted);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .table-transcripts {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .table-transcripts th {
            padding: 13px 18px;
            font: 10px 'DM Mono', monospace;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            background: #faf9f5;
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }
        .table-transcripts td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        .table-transcripts tr:hover td {
            background: #fbfbfa;
        }
        .table-transcripts tr.active-row td {
            background: #f3f7f3;
        }

        .row-title {
            font-weight: 600;
            color: var(--ink);
            display: block;
            margin-bottom: 2px;
        }
        .row-file {
            font: 11px 'DM Mono', monospace;
            color: var(--muted);
        }
        .row-snippet {
            max-width: 280px;
            color: #40453c;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .row-audio-player audio {
            width: 170px;
            height: 32px;
        }
        .row-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        /* History Empty */
        .history-empty {
            padding: 56px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .history-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f2f1ea;
            display: grid;
            place-items: center;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .history-empty strong {
            font-size: 15px;
            font-weight: 600;
        }
        .history-empty p {
            font-size: 13px;
            color: var(--muted);
            max-width: 380px;
            margin: 0;
            line-height: 1.6;
        }

        /* Pagination */
        .pagination-bar {
            padding: 14px 20px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
            background: #faf9f5;
        }
        .pagination-links {
            display: flex;
            gap: 6px;
        }

        /* Footer */
        .footer {
            margin-top: 48px;
            border-top: 1px solid var(--line);
            padding: 24px 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--muted);
            font: 11px 'DM Mono', monospace;
            flex-wrap: wrap;
            gap: 12px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }

        @keyframes pulse {
            50% { box-shadow: 0 0 0 9px #b94e3c15; }
        }

        @media (max-width: 920px) {
            .workspace { grid-template-columns: 1fr; }
            .hero { padding: 40px 0 24px; }
            .shell { padding: 0 20px 40px; }
        }
        @media (max-width: 540px) {
            .topbar { height: 64px; }
            .top-note { display: none; }
            .history-head { flex-direction: column; align-items: flex-start; gap: 8px; }
            .row-audio-player audio { width: 140px; }
        }
    </style>
</head>
<body>
<div class="shell">
    <!-- <header class="topbar" aria-label="Navigasi utama">
        <a class="brand" href="{{ route('home') }}" aria-label="Suara, beranda">
            <span class="brand-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 10v4M8 6v12M12 3v18M16 7v10M20 10v4"/></svg>
            </span>
            suara
        </a>
        <span class="top-note">RUANG KERJA TRANSKRIPSI &nbsp;·&nbsp; DATABASE SQLITE &nbsp;·&nbsp; BAHASA INDONESIA</span>
    </header> -->

    <main style="margin-top: 12px;">
        <!-- <section class="hero">
            <div class="eyebrow">Dari suara menjadi cerita</div>
            <h1>Setiap percakapan,<br><em>tertangkap dan tersimpan.</em></h1>
            <p>Rekam suara secara langsung atau unggah audio. File attachment disimpan ke database SQLite sehingga dapat diputar kembali kapan saja.</p>
        </section> -->

        @if (session('success') && ! session('report_modal'))
            <div class="alert alert-success" role="alert">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:inherit;font-size:16px;">&times;</button>
            </div>
        @endif

        @if (session('report_error') && ! session('report_modal'))
            <div class="alert alert-error" role="alert">{{ session('report_error') }}</div>
        @endif

        @if ($errors->has('audio'))
            <div class="alert alert-error" role="alert">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ $errors->first('audio') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:inherit;font-size:16px;">&times;</button>
            </div>
        @endif

        <!-- WORKSPACE -->
        <section class="workspace" aria-label="Transkripsi audio">
            <!-- PANEL 1: INPUT AUDIO -->
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title">Sumber audio</span>
                    <span class="step">LANGKAH 01 / 02</span>
                </div>
                <div class="panel-body">
                    <div class="tabs" role="tablist" aria-label="Pilih sumber audio">
                        <button class="tab active" type="button" role="tab" id="tab-record" aria-selected="true" data-mode="record">Rekam suara</button>
                        <button class="tab" type="button" role="tab" id="tab-upload" aria-selected="false" data-mode="upload">Unggah file</button>
                    </div>

                    <!-- TAB 1: REKAM SUARA -->
                    <div id="record-pane" role="tabpanel">
                        <div class="recordbox" id="recordbox">
                            <div class="record-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="3" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3m-4 0h8"/></svg>
                            </div>
                            <strong id="record-title">Mulai percakapan Anda</strong>
                            <p id="record-description">Gunakan mikrofon perangkat Anda untuk merekam percakapan</p>
                            <span class="timer" id="timer">00:00</span>
                        </div>
                        <div class="actions">
                            <button class="btn primary" id="record-button" type="button">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                Mulai merekam
                            </button>
                            <button class="btn danger" id="stop-button" type="button" hidden>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="12" height="12" x="6" y="6" rx="2"/></svg>
                                Hentikan rekaman
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: UNGGAH / FORM KIRIM -->
                    <form id="upload-form" action="{{ route('transcribe') }}" method="POST" enctype="multipart/form-data" hidden>
                        @csrf
                        <input type="hidden" name="source_type" id="source-type" value="upload">
                        
                        <label class="dropzone" id="dropzone" for="audio-file">
                            <span class="upload-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m-4 4 4-4 4 4M5 14v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5"/></svg>
                            </span>
                            <strong id="dropzone-title">Pilih rekaman untuk ditranskripsikan</strong>
                            <span id="dropzone-desc">atau seret & jatuhkan file audio di sini</span>
                            <span class="file-name" id="file-name">WAV, MP3, M4A, WEBM, OGG · Maks. 100 MB</span>
                        </label>
                        <input class="sr-only" id="audio-file" name="audio" type="file" accept="audio/*,.mp3,.mp4,.mpeg,.mpga,.m4a,.wav,.webm,.ogg,.3gp,.3gpp" required>

                        <!-- Audio Preview Player Sebelum Submit -->
                        <div class="preview-box" id="preview-box" style="display: none;">
                            <div class="preview-header">
                                <span>Pratinjau audio sebelum transkrip:</span>
                                <span id="preview-badge" class="badge badge-upload">File Terpilih</span>
                            </div>
                            <audio id="preview-audio" controls></audio>
                        </div>

                        <div class="submit-row">
                            <span class="format-note">Audio akan disimpan permanen ke database SQLite.</span>
                            <button class="btn primary" type="submit" id="upload-submit" disabled>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>
                                Transkripsikan sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PANEL 2: HASIL TRANSKRIP AKTIF -->
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title">Hasil transkrip</span>
                    <span class="step">LANGKAH 02 / 02</span>
                </div>

                @if ($activeTranscript)
                    <div class="result-content">
                        <div class="result-meta">
                            <div class="meta-info">
                                <div class="meta-title">
                                    @if ($activeTranscript->source_type === 'record')
                                        <span class="badge badge-record">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg>
                                            Rekaman
                                        </span>
                                    @else
                                        <span class="badge badge-upload">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            Unggahan
                                        </span>
                                    @endif
                                    <span>{{ $activeTranscript->title ?: $activeTranscript->audio_name }}</span>
                                </div>
                                <span class="meta-details">
                                    {{ $activeTranscript->audio_name }} &bull; {{ $activeTranscript->formatted_size }} &bull; Durasi transkripsi: {{ $activeTranscript->formatted_transcription_duration ?? 'tidak tersedia' }} &bull; {{ $activeTranscript->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <div class="result-actions">
                                @if ($activeTranscript->formatted_report)
                                    <button type="button" class="btn sm" id="copy-report-button" title="Salin laporan WhatsApp">Salin laporan</button>
                                @endif
                                <button type="button" class="btn sm" id="copy-button" title="Salin seluruh isi transkrip">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    Salin
                                </button>
                                <a href="{{ route('transcripts.download', $activeTranscript) }}" class="btn sm" title="Unduh transkrip format .txt">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Unduh .txt
                                </a>
                            </div>
                        </div>

                        <!-- PEMUTAR AUDIO UNTUK RECORDING/UPLOAD AKTIF -->
                        <div class="player-card">
                            <div class="player-card-label">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                                Pemutar File Audio Attachment
                            </div>
                            <audio controls src="{{ $activeTranscript->audio_url }}" preload="metadata"></audio>
                        </div>

                        <!-- KONTEN TRANSKRIPSI -->
                        @if ($activeTranscript->formatted_report)
                            <div class="transcript-box" style="margin-bottom:16px;">
                                <strong>Laporan kunjungan (format WhatsApp)</strong>
                                <pre class="transcript-text" id="formatted-report" style="white-space:pre-wrap;font:inherit;margin:12px 0 0;">{{ $activeTranscript->formatted_report }}</pre>
                            </div>
                        @endif
                        <div class="transcript-box">
                            <strong>Transkrip asli</strong>
                            <p class="transcript-text" id="transcript-text">{{ $activeTranscript->text }}</p>
                        </div>
                    </div>
                @else
                    <div class="result-empty" id="result-empty">
                        <span class="result-symbol">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v6h5M9 14h6m-6 4h6"/></svg>
                        </span>
                        <strong>Transkrip Anda akan muncul di sini</strong>
                        <p>Setelah audio diproses, percakapan tampil rapi dan file dapat diputar kembali kapan saja.</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- TABLE RIWAYAT TRANSKRIPSI (DATABASE SQLITE) -->
        <section class="history-section" id="riwayat" aria-label="Riwayat transkripsi">
            <div class="history-head">
                <div class="history-title">
                    <h2>Riwayat Transkripsi & Attachment</h2>
                    <span class="history-count">{{ $transcripts->total() }} Tersimpan</span>
                </div>
                <span class="history-note">Tersimpan dalam database SQLite (tabel: <code>transcripts</code>)</span>
            </div>

            @if ($transcripts->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-transcripts">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 100px;">Sumber</th>
                                <th>Judul & Nama File</th>
                                <th style="width: 200px;">Pemutar Audio</th>
                                <th>Ringkasan Transkrip</th>
                                <th style="width: 130px;">Tanggal</th>
                                <th style="width: 160px; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transcripts as $index => $item)
                                <tr class="{{ optional($activeTranscript)->id === $item->id ? 'active-row' : '' }}">
                                    <td style="font: 11px 'DM Mono', monospace; color: var(--muted);">
                                        {{ $transcripts->firstItem() + $index }}
                                    </td>
                                    <td>
                                        @if ($item->source_type === 'record')
                                            <span class="badge badge-record">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg>
                                                Rekam
                                            </span>
                                        @else
                                            <span class="badge badge-upload">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                                Unggah
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="row-title">{{ $item->title ?: $item->audio_name }}</span>
                                        <span class="row-file">{{ $item->audio_name }} &bull; {{ $item->formatted_size }}</span>
                                    </td>
                                    <td class="row-audio-player">
                                        <audio controls preload="none" src="{{ $item->audio_url }}" title="Putar {{ $item->audio_name }}"></audio>
                                    </td>
                                    <td>
                                        <div class="row-snippet" title="{{ $item->text }}">
                                            {{ Str::limit($item->text, 95) }}
                                        </div>
                                    </td>
                                    <td style="font: 11px 'DM Mono', monospace; color: var(--muted);">
                                        {{ $item->created_at->format('d/m/Y H:i') }}<br><span style="font:11px 'DM Sans',sans-serif;">Proses: {{ $item->formatted_transcription_duration ?? '—' }}</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="row-actions" style="justify-content: flex-end;">
                                            <button type="button" class="btn sm" onclick="showTranscriptModal({{ json_encode($item->text) }}, {{ json_encode($item->formatted_report) }}, {{ json_encode(route('transcripts.report.regenerate', $item)) }})" title="Lihat transkrip dan laporan">Lihat</button>
                                            <button type="button" class="btn sm" onclick="copyRowText(this, {{ json_encode($item->text) }})" title="Salin teks">
                                                Salin
                                            </button>
                                            <a href="{{ route('transcripts.download', $item) }}" class="btn sm" title="Unduh .txt">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            </a>
                                            <form action="{{ route('transcripts.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus riwayat dan file audio ini?');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn sm danger" title="Hapus dari riwayat">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($transcripts->hasPages())
                    <div class="pagination-bar">
                        <span>Menampilkan {{ $transcripts->firstItem() }} - {{ $transcripts->lastItem() }} dari {{ $transcripts->total() }} transkrip</span>
                        <div class="pagination-links">
                            @if ($transcripts->onFirstPage())
                                <button class="btn sm" disabled>&laquo; Sebelumnya</button>
                            @else
                                <a href="{{ $transcripts->previousPageUrl() }}" class="btn sm">&laquo; Sebelumnya</a>
                            @endif

                            @if ($transcripts->hasMorePages())
                                <a href="{{ $transcripts->nextPageUrl() }}" class="btn sm">Berikutnya &raquo;</a>
                            @else
                                <button class="btn sm" disabled>Berikutnya &raquo;</button>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="history-empty">
                    <div class="history-empty-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <strong>Belum ada riwayat transkripsi</strong>
                    <p>Mulai dengan merekam suara atau mengunggah audio. File rekaman dan transkrip akan otomatis disimpan dan dapat diputar kembali kapan saja di sini.</p>
                </div>
            @endif
        </section>
        <div id="transcript-modal" role="dialog" aria-modal="true" aria-labelledby="transcript-modal-title" hidden style="position:fixed;inset:0;background:rgba(20,24,22,.55);z-index:1000;padding:24px;overflow:auto;">
        <div style="max-width:760px;margin:5vh auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;"><strong id="transcript-modal-title">Detail transkrip tersimpan</strong><button type="button" class="btn sm" onclick="closeTranscriptModal()" aria-label="Tutup">Tutup</button></div>
            <div id="modal-status" role="status" style="margin-top:12px;" hidden></div>
            <div class="transcript-box" style="margin-top:18px;"><strong>Transkrip asli</strong><pre id="modal-transcript" class="transcript-text" style="white-space:pre-wrap;font:inherit;margin:12px 0 0;"></pre></div>
            <div class="transcript-box" style="margin-top:16px;"><strong>Laporan kunjungan (format WhatsApp)</strong><pre id="modal-report" class="transcript-text" style="white-space:pre-wrap;font:inherit;margin:12px 0 0;"></pre></div>
            <form id="regenerate-report-form" method="POST" style="margin-top:16px;">
                @csrf
                <button type="submit" class="btn primary">Buat ulang laporan dengan AI</button>
            </form>
        </div>
    </div>
</main>

    <footer class="footer">
        <span>SUARA &nbsp;&bull;&nbsp; RIWAYAT AUDIO & TRANSKRIPSI CERDAS</span>
        <span>DATABASE: SQLite (table <code>transcripts</code>) &nbsp;&bull;&nbsp; MODEL: OpenAI Whisper</span>
    </footer>
</div>

<script>
    function showTranscriptModal(transcript, report, action, status, statusIsError) {
        document.getElementById('modal-transcript').textContent = transcript || '';
        document.getElementById('modal-report').textContent = report || 'Belum ada laporan yang dibuat.';
        document.getElementById('regenerate-report-form').action = action;

        const statusBox = document.getElementById('modal-status');
        if (status) {
            statusBox.hidden = false;
            statusBox.textContent = status;
            statusBox.style.marginTop = '12px';
            statusBox.style.padding = '10px 12px';
            statusBox.style.borderRadius = '8px';
            statusBox.style.font = '13px/1.5 "DM Sans", sans-serif';
            if (statusIsError) {
                statusBox.style.background = '#fdecea';
                statusBox.style.color = '#b3261e';
                statusBox.style.border = '1px solid #f5c6c2';
            } else {
                statusBox.style.background = '#e8f5ec';
                statusBox.style.color = '#1e6b3a';
                statusBox.style.border = '1px solid #c2e6cf';
            }
        } else {
            statusBox.hidden = true;
            statusBox.textContent = '';
        }

        document.getElementById('transcript-modal').hidden = false;
    }

    function closeTranscriptModal() {
        document.getElementById('transcript-modal').hidden = true;
    }

    @if (session('report_modal') && $activeTranscript)
    showTranscriptModal(
        {{ json_encode($activeTranscript->text) }},
        {{ json_encode($activeTranscript->formatted_report) }},
        {{ json_encode(route('transcripts.report.regenerate', $activeTranscript)) }},
        {{ json_encode(session('report_error') ?: session('success')) }},
        {{ json_encode((bool) session('report_error')) }}
    );
    @endif

    // Tab switching
    const tabRecord = document.getElementById('tab-record');
    const tabUpload = document.getElementById('tab-upload');
    const recordPane = document.getElementById('record-pane');
    const uploadForm = document.getElementById('upload-form');
    const sourceTypeInput = document.getElementById('source-type');

    function switchTab(mode) {
        const isRecord = mode === 'record';
        tabRecord.classList.toggle('active', isRecord);
        tabRecord.setAttribute('aria-selected', isRecord ? 'true' : 'false');
        tabUpload.classList.toggle('active', !isRecord);
        tabUpload.setAttribute('aria-selected', !isRecord ? 'true' : 'false');
        recordPane.hidden = !isRecord;
        uploadForm.hidden = isRecord;
    }

    tabRecord.addEventListener('click', () => switchTab('record'));
    tabUpload.addEventListener('click', () => {
        sourceTypeInput.value = 'upload';
        switchTab('upload');
    });

    // Recording logic
    let recorder;
    let chunks = [];
    let stream;
    let startedAt = 0;
    let timerInterval;
    const recordButton = document.getElementById('record-button');
    const stopButton = document.getElementById('stop-button');
    const recordbox = document.getElementById('recordbox');
    const timerDisplay = document.getElementById('timer');
    const previewBox = document.getElementById('preview-box');
    const previewAudio = document.getElementById('preview-audio');
    const previewBadge = document.getElementById('preview-badge');

    const formatTime = seconds => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;

    recordButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const preferred = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4'].find(type => MediaRecorder.isTypeSupported(type));
            recorder = new MediaRecorder(stream, preferred ? { mimeType: preferred } : undefined);
            chunks = [];

            recorder.ondataavailable = event => {
                if (event.data && event.data.size > 0) {
                    chunks.push(event.data);
                }
            };

            recorder.onstop = () => {
                stream.getTracks().forEach(track => track.stop());
                const blob = new Blob(chunks, { type: recorder.mimeType || 'audio/webm' });
                const ext = blob.type.includes('mp4') ? 'm4a' : 'webm';
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                const timestampStr = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
                const filename = `rekaman-${timestampStr}.${ext}`;
                const file = new File([blob], filename, { type: blob.type });

                const transfer = new DataTransfer();
                transfer.items.add(file);
                const fileInput = document.getElementById('audio-file');
                fileInput.files = transfer.files;

                // Update UI and source_type
                sourceTypeInput.value = 'record';
                document.getElementById('file-name').textContent = `${file.name} · ${(file.size / 1024).toFixed(1)} KB`;
                document.getElementById('upload-submit').disabled = false;

                // Pratinjau audio rekaman
                const audioBlobUrl = URL.createObjectURL(blob);
                previewAudio.src = audioBlobUrl;
                previewBadge.className = 'badge badge-record';
                previewBadge.textContent = 'Rekaman Suara Baru';
                previewBox.style.display = 'flex';

                // Beralih ke tab form upload
                switchTab('upload');
                document.getElementById('dropzone-title').textContent = 'Rekaman Suara Siap Ditranskripsikan';
                document.getElementById('dropzone-desc').textContent = 'Klik tombol transkripsi di bawah atau putar pratinjau audio terlebih dahulu';
            };

            recorder.start();
            startedAt = Date.now();
            recordbox.classList.add('recording');
            document.getElementById('record-title').textContent = 'Sedang merekam percakapan…';
            document.getElementById('record-description').textContent = 'Bicaralah dengan jelas. Klik hentikan jika sudah selesai.';
            recordButton.hidden = true;
            stopButton.hidden = false;
            timerInterval = setInterval(() => {
                timerDisplay.textContent = formatTime(Math.floor((Date.now() - startedAt) / 1000));
            }, 500);
        } catch (error) {
            console.error(error);
            alert('Mikrofon tidak dapat diakses. Mohon periksa izin mikrofon browser Anda, atau gunakan tab "Unggah file".');
        }
    });

    stopButton.addEventListener('click', () => {
        clearInterval(timerInterval);
        if (recorder && recorder.state !== 'inactive') {
            recorder.stop();
        }
        recordbox.classList.remove('recording');
        recordButton.hidden = false;
        stopButton.hidden = true;
    });

    // File input & Drag-drop logic
    const fileInput = document.getElementById('audio-file');
    const dropzone = document.getElementById('dropzone');

    function handleFileSelected(file) {
        if (!file) return;
        sourceTypeInput.value = 'upload';
        const sizeMb = (file.size / 1048576).toFixed(1);
        const sizeKb = (file.size / 1024).toFixed(1);
        const displaySize = file.size >= 1048576 ? `${sizeMb} MB` : `${sizeKb} KB`;

        document.getElementById('file-name').textContent = `${file.name} · ${displaySize}`;
        document.getElementById('upload-submit').disabled = false;

        // Preview player
        const objectUrl = URL.createObjectURL(file);
        previewAudio.src = objectUrl;
        previewBadge.className = 'badge badge-upload';
        previewBadge.textContent = 'File Audio Unggahan';
        previewBox.style.display = 'flex';
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFileSelected(fileInput.files[0]);
        }
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelected(e.dataTransfer.files[0]);
        }
    });

    // Form submit loading feedback
    uploadForm.addEventListener('submit', () => {
        const btn = document.getElementById('upload-submit');
        btn.disabled = true;
        btn.innerHTML = `
            <svg style="animation:spin 1s linear infinite" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity=".8"/></svg>
            Mentranskripsikan & Menyimpan…
        `;
    });

    const reportCopyBtn = document.getElementById('copy-report-button');
    if (reportCopyBtn) {
        reportCopyBtn.addEventListener('click', async () => {
            await navigator.clipboard.writeText(document.getElementById('formatted-report').innerText);
            reportCopyBtn.textContent = 'Tersalin!';
            setTimeout(() => reportCopyBtn.textContent = 'Salin laporan', 1800);
        });
    }

    // Copy to clipboard helper
    const copyBtn = document.getElementById('copy-button');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const text = document.getElementById('transcript-text').innerText;
            try {
                await navigator.clipboard.writeText(text);
                const originalHtml = copyBtn.innerHTML;
                copyBtn.innerHTML = `
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Tersalin!
                `;
                setTimeout(() => copyBtn.innerHTML = originalHtml, 1800);
            } catch (err) {
                console.error('Gagal menyalin:', err);
            }
        });
    }

    // Function to copy text from table row
    async function copyRowText(button, text) {
        try {
            await navigator.clipboard.writeText(text);
            const originalText = button.textContent;
            button.textContent = 'Tersalin!';
            setTimeout(() => button.textContent = originalText, 1800);
        } catch (err) {
            console.error('Gagal menyalin teks tabel:', err);
        }
    }
</script>
<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
</body>
</html>
