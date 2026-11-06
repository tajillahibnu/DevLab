<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Pengaturan transkripsi</title></head>
<body style="font-family: sans-serif; max-width: 680px; margin: 3rem auto; padding: 0 1rem; color: #202820">
    <a href="{{ route('home') }}">← Kembali ke transkripsi</a>
    <h1>Pengaturan layanan Whisper</h1>
    @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    <form method="POST" action="{{ route('settings.transcription.update') }}">
        @csrf
        <fieldset><legend>Layanan transkripsi untuk upload</legend>
            @foreach ($providers as $key => $provider)
                <label style="display:block; margin:1rem 0"><input type="radio" name="provider" value="{{ $key }}" @checked(old('provider', $selected) === $key)> {{ $provider['name'] }} — {{ $provider['url'] }}
                    @if ($key === 'speaches')<br><small>OpenAI-compatible. Model default: Systran/faster-whisper-small; unduh model di Speaches sebelum digunakan.</small>@endif
                </label>
            @endforeach
            <p style="max-width:30rem">WhisperLive menyediakan WebSocket streaming di port 9003, dan API REST untuk unggah file di port 9004.</p>
        </fieldset>
        <label>Model (Speaches / Whisper Server)<br><input name="model" value="{{ old('model', $model) }}" maxlength="200"></label>
        <p>OneRahmet mengabaikan model yang dikirim dan memakai konfigurasi server.</p>
        <button type="submit">Simpan pilihan</button>
    </form>
    <form method="POST" action="{{ route('settings.transcription.download-model') }}">
        @csrf
        <input type="hidden" name="model" value="{{ old('model', $model) }}">
        <button type="submit">Unduh model ke Speaches</button>
    </form>
</body>
</html>
