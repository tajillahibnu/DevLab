<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Throwable;

class TranscribeAudioController extends Controller
{
    public function __invoke(Request $request)
    {
        ini_set('max_execution_time', '300');
        set_time_limit(300);

        $validated = $request->validate([
            'audio' => ['required', File::types(['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm', 'ogg'])->max('100mb')],
        ], [
            'audio.required' => 'Pilih atau rekam audio terlebih dahulu.',
            'audio.max' => 'Ukuran audio maksimal 100 MB.',
            'audio.mimes' => 'Format audio tidak didukung.',
        ]);

        $originalName = $validated['audio']->getClientOriginalName();
        $path = $validated['audio']->store('transcriptions', 'local');

        try {
            $whisperUrl = env('WHISPER_URL')
                ?: preg_replace('#/v1/?$#', '', config('ai.providers.openai.url') ?: 'http://localhost:9000');
            $whisperUrl = rtrim($whisperUrl, '/');

            $fileContents = Storage::disk('local')->get($path);

            $response = Http::timeout(300)
                ->attach('audio_file', $fileContents, $originalName)
                ->post("{$whisperUrl}/asr?task=transcribe&language=id&output=json&encode=true");

            if (! $response->successful()) {
                $detail = $response->json('detail') ?? $response->body() ?? 'Server Whisper merespons error.';
                throw new \RuntimeException(is_array($detail) ? json_encode($detail) : $detail);
            }

            $data = $response->json();
            $transcript = trim($data['text'] ?? '');

            if (empty($transcript)) {
                $transcript = '(Tidak ada suara atau percakapan yang terdeteksi)';
            }

            return back()->with('transcript', $transcript)
                ->with('segments', $data['segments'] ?? [])
                ->with('audio_name', $originalName);
        } catch (Throwable $exception) {
            report($exception);

            $message = $exception->getMessage();
            if (preg_match('/"detail"\s*:\s*"([^"]+)"/', $message, $matches)) {
                $detail = $matches[1];
            } else {
                $detail = $message;
            }

            return back()->withInput()->withErrors([
                'audio' => 'Transkripsi gagal: '.$detail,
            ]);
        } finally {
            Storage::disk('local')->delete($path);
        }
    }
}
