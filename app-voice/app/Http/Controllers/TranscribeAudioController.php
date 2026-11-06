<?php

namespace App\Http\Controllers;

use App\Ai\Agents\VisitReportAgent;
use App\Models\Transcript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Laravel\Ai\Enums\Lab;
use Throwable;

class TranscribeAudioController extends Controller
{
    public function regenerateReport(Transcript $transcript)
    {
        try {
            $provider = config('ai-report.provider');
            $model = config('ai-report.model');

            if (! in_array($provider, ['openai-compatible', 'ollama'], true)) {
                throw new \RuntimeException('AI_REPORT_PROVIDER must be openai-compatible or ollama.');
            }

            if (! is_string($model) || trim($model) === '') {
                throw new \RuntimeException('AI_REPORT_MODEL is required.');
            }

            $aiProvider = $provider === 'openai-compatible' ? Lab::OpenAICompatible : Lab::Ollama;
            $report = (new VisitReportAgent)->prompt(
                $transcript->text,
                provider: $aiProvider,
                model: $model,
                timeout: (int) config('ai-report.timeout', 120),
            );

            $transcript->update(['formatted_report' => trim($report->text)]);

            return redirect()->route('home', ['active' => $transcript->id])
                ->with('report_modal', true)
                ->with('success', 'Laporan berhasil dibuat ulang.');
        } catch (Throwable) {
            return redirect()->route('home', ['active' => $transcript->id])
                ->with('report_modal', true)
                ->with('report_error', 'Laporan AI gagal dibuat ulang. Periksa AI_REPORT_PROVIDER, AI_REPORT_URL, AI_REPORT_MODEL, dan koneksi penyedia AI. Transkrip asli tetap tersimpan.');
        }
    }

    public function __invoke(Request $request)
    {
        ini_set('max_execution_time', '300');
        set_time_limit(300);

        $validated = $request->validate([
            'audio' => ['required', 'file', 'max:102400', 'extensions:mp3,mp4,mpeg,mpga,m4a,wav,webm,ogg,3gp,3gpp'],
            'source_type' => ['nullable', 'string', 'in:record,upload'],
            'provider' => ['nullable', 'string', 'in:onerahmet,speaches,scribecrate,whisperlive'],
        ], [
            'audio.required' => 'Pilih atau rekam audio terlebih dahulu.',
            'audio.max' => 'Ukuran audio maksimal 100 MB.',
            'audio.extensions' => 'Format audio tidak didukung. Gunakan MP3, MP4, M4A, WAV, WebM, OGG, atau 3GP.',
        ]);

        $audioFile = $validated['audio'];
        $originalName = $audioFile->getClientOriginalName();
        $sourceType = $request->input('source_type', str_starts_with($originalName, 'rekaman-') ? 'record' : 'upload');
        $provider = $validated['provider'] ?? null;
        if ($provider !== null) {
            TranscriptionSettingsController::saveSelection($provider, TranscriptionSettingsController::selection()['model']);
        }
        $transcriptionStartedAt = microtime(true);

        // Store audio permanently on public disk so it can be streamed/played back
        $storedPath = $audioFile->store('transcriptions', 'public');
        $fullDiskPath = Storage::disk('public')->path($storedPath);

        try {
            $selection = TranscriptionSettingsController::selection();
            $provider = $selection['provider'];
            $baseUrl = [
                'onerahmet' => 'http://127.0.0.1:9000',
                'speaches' => 'http://127.0.0.1:9001',
                'scribecrate' => 'http://127.0.0.1:9002',
                'whisperlive' => 'http://127.0.0.1:9004',
            ][$provider];
            $baseUrl = rtrim((string) config("services.whisper.{$provider}_url", $baseUrl), '/');
            $fileContents = file_get_contents($fullDiskPath);
            $request = Http::timeout(300);

            if ($provider === 'onerahmet') {
                $response = $request->attach('audio_file', $fileContents, $originalName)
                    ->post("{$baseUrl}/asr?task=transcribe&language=id&output=json&encode=true");
            } else {
                $request = $request->attach('file', $fileContents, $originalName);
                if ($provider === 'scribecrate') {
                    $apiKey = config('services.whisper.scribecrate_api_key');
                    if (is_string($apiKey) && $apiKey !== '') {
                        $request = $request->withToken($apiKey);
                    }
                }
                $model = in_array($provider, ['scribecrate', 'whisperlive'], true) ? 'whisper-1' : $selection['model'];
                $response = $request->post("{$baseUrl}/v1/audio/transcriptions", [
                    'model' => $model,
                    'language' => 'id',
                ]);
            }

            if (! $response->successful()) {
                $detail = $response->json('detail') ?? $response->body() ?? 'Server Whisper merespons error.';
                throw new \RuntimeException(is_array($detail) ? json_encode($detail) : $detail);
            }

            $data = $response->json();
            $transcriptText = trim($data['text'] ?? '');

            if (empty($transcriptText)) {
                $transcriptText = '(Tidak ada suara atau percakapan yang terdeteksi)';
            }

            // Create persistent record in database table 'transcripts'
            $transcript = Transcript::create([
                'title' => pathinfo($originalName, PATHINFO_FILENAME),
                'audio_name' => $originalName,
                'audio_path' => $storedPath,
                'mime_type' => $audioFile->getClientMimeType() ?: 'audio/wav',
                'file_size' => $audioFile->getSize() ?: 0,
                'source_type' => $sourceType,
                'text' => $transcriptText,
                'language' => 'id',
                'transcription_provider' => $provider,
                'segments' => $data['segments'] ?? [],
                'transcription_duration_seconds' => (int) round(microtime(true) - $transcriptionStartedAt),
            ]);

            return redirect()->route('home', ['active' => $transcript->id])
                ->with('success', 'Audio berhasil ditranskripsikan dan disimpan ke riwayat!');
        } catch (Throwable $exception) {
            report($exception);

            // Clean up stored file if transcription failed
            if (Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }

            $message = $exception->getMessage();
            if (preg_match('/"detail"\s*:\s*"([^"]+)"/', $message, $matches)) {
                $detail = $matches[1];
            } else {
                $detail = $message;
            }

            return back()->withInput()->withErrors([
                'audio' => 'Transkripsi gagal: '.$detail,
            ]);
        }
    }
}
