<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TranscriptionSettingsController extends Controller
{
    private const PROVIDERS = [
        'onerahmet' => ['name' => 'OneRahmet Whisper', 'url' => 'http://127.0.0.1:9000'],
        'speaches' => ['name' => 'Speaches', 'url' => 'http://127.0.0.1:9001'],
        'scribecrate' => ['name' => 'Whisper Server (hwdsl2)', 'url' => 'http://127.0.0.1:9002'],
        'whisperlive' => ['name' => 'WhisperLive (REST)', 'url' => 'http://127.0.0.1:9004'],
    ];

    public function show()
    {
        return view('settings.transcription', [
            'providers' => self::PROVIDERS,
            'selected' => $this->selection()['provider'],
            'model' => $this->selection()['model'],
        ]);
    }

    public function downloadModel(Request $request)
    {
        $validated = $request->validate([
            'model' => ['required', 'string', 'max:200'],
        ]);

        $model = trim($validated['model']);
        abort_if($model === '', 422);

        $response = Http::timeout(300)->post('http://127.0.0.1:9001/v1/models/'.implode('/', array_map('rawurlencode', explode('/', $model))));

        if (! $response->successful()) {
            return back()->withErrors(['model' => 'Pengunduhan model gagal: '.($response->json('detail') ?? $response->body())]);
        }

        return redirect()->route('settings.transcription')->with('success', 'Model berhasil diunduh ke Speaches.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:onerahmet,speaches,scribecrate,whisperlive'],
            'model' => ['nullable', 'string', 'max:200'],
        ]);

        self::saveSelection($validated['provider'], $validated['model'] ?? null);

        return redirect()->route('settings.transcription')->with('success', 'Pengaturan transkripsi berhasil disimpan.');
    }

    public static function saveSelection(string $provider, ?string $model = null): void
    {
        Storage::disk('local')->put('transcription-settings.json', json_encode([
            'provider' => $provider,
            'model' => trim($model ?? '') ?: 'Systran/faster-whisper-small',
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    public static function selection(): array
    {
        $saved = json_decode(Storage::disk('local')->get('transcription-settings.json', '{}'), true) ?: [];
        $provider = in_array($saved['provider'] ?? null, array_keys(self::PROVIDERS), true) ? $saved['provider'] : 'onerahmet';

        return [
            'provider' => $provider,
            'model' => is_string($saved['model'] ?? null) && $saved['model'] !== '' ? $saved['model'] : 'Systran/faster-whisper-small',
        ];
    }
}
