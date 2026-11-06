<?php

namespace Tests\Feature;

use App\Models\Transcript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranscriptTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_home_page_with_empty_history(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('suara');
        $response->assertSee('Belum ada riwayat transkripsi');
    }

    public function test_it_transcribes_uploaded_audio_and_persists_to_database(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('transcription-settings.json', json_encode(['provider' => 'whisperlive']));
        Http::fake([
            'http://127.0.0.1:9000/asr*' => Http::response([
                'text' => 'Halo ini adalah contoh suara untuk pengujian transkripsi.',
                'segments' => [
                    ['start' => 0.0, 'end' => 2.5, 'text' => 'Halo ini adalah contoh suara.'],
                ],
            ], 200),
        ]);

        $audio = UploadedFile::fake()->create('sample-interview.mp3', 250, 'audio/mpeg');

        $response = $this->post(route('transcribe'), [
            'audio' => $audio,
            'source_type' => 'upload',
            'provider' => 'onerahmet',
        ]);

        $this->assertDatabaseCount('transcripts', 1);
        $transcript = Transcript::first();

        $this->assertEquals('sample-interview', $transcript->title);
        $this->assertEquals('sample-interview.mp3', $transcript->audio_name);
        $this->assertEquals('upload', $transcript->source_type);
        $this->assertSame('onerahmet', $transcript->transcription_provider);
        $this->assertEquals('Halo ini adalah contoh suara untuk pengujian transkripsi.', $transcript->text);
        $this->assertNull($transcript->formatted_report);
        $this->assertIsNumeric($transcript->transcription_duration_seconds);

        // Verify audio file stored in public disk
        Storage::disk('public')->assertExists($transcript->audio_path);

        $response->assertRedirect(route('home', ['active' => $transcript->id]));
    }

    public function test_it_accepts_whatsapp_mp4_video_upload_for_transcription(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake([
            'http://127.0.0.1:9000/asr*' => Http::response(['text' => 'Audio dalam video berhasil ditranskripsikan.', 'segments' => []], 200),
        ]);

        $response = $this->post(route('transcribe'), [
            'audio' => UploadedFile::fake()->createWithContent('WhatsApp Audio 2026-11-02 at 19.43.16.mp4', "\x00\x00\x00\x18ftyp3gp4\x00\x00\x00\x00isom3gp4\x00\x00\x00\x01mdat"),
            'source_type' => 'upload',
            'provider' => 'onerahmet',
        ]);

        $transcript = Transcript::firstOrFail();
        $this->assertSame('Audio dalam video berhasil ditranskripsikan.', $transcript->text);
        $this->assertSame('onerahmet', $transcript->transcription_provider);
        $this->assertSame('application/mp4', $transcript->mime_type);
        Storage::disk('public')->assertExists($transcript->audio_path);
        $response->assertRedirect(route('home', ['active' => $transcript->id]));
    }

    public function test_transcription_saves_without_generating_ai_report(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake([
            'http://127.0.0.1:9000/asr*' => Http::response(['text' => 'Transkrip tetap tersimpan.', 'segments' => []], 200),
        ]);

        $response = $this->post(route('transcribe'), [
            'audio' => UploadedFile::fake()->create('visit.wav', 100, 'audio/wav'),
            'source_type' => 'upload',
            'provider' => 'onerahmet',
        ]);

        $transcript = Transcript::firstOrFail();
        $this->assertSame('Transkrip tetap tersimpan.', $transcript->text);
        $this->assertNull($transcript->formatted_report);
        Storage::disk('public')->assertExists($transcript->audio_path);
        $response->assertRedirect(route('home', ['active' => $transcript->id]));
        $response->assertSessionMissing('report_error');
    }

    public function test_it_regenerates_report_without_changing_transcript_text(): void
    {
        Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => '*Laporan baru*']]]], 200)]);
        config(['ai-report.provider' => 'openai-compatible', 'ai-report.model' => 'nara-ibnu']);

        $transcript = Transcript::create([
            'title' => 'Kunjungan',
            'audio_name' => 'kunjungan.wav',
            'audio_path' => 'transcriptions/kunjungan.wav',
            'mime_type' => 'audio/wav',
            'file_size' => 100,
            'source_type' => 'upload',
            'text' => 'Teks transkrip asli.',
        ]);

        $response = $this->post(route('transcripts.report.regenerate', $transcript));

        $transcript->refresh();
        $this->assertSame('Teks transkrip asli.', $transcript->text);
        $this->assertSame('*Laporan baru*', $transcript->formatted_report);
        $response->assertRedirect(route('home', ['active' => $transcript->id]));
    }

    public function test_report_regeneration_failure_preserves_transcript_and_existing_report(): void
    {
        Http::fake(['*/chat/completions' => Http::response(['error' => ['message' => 'AI endpoint unavailable']], 503)]);
        config(['ai-report.provider' => 'openai-compatible', 'ai-report.model' => 'nara-ibnu']);

        $transcript = Transcript::create([
            'title' => 'Kunjungan',
            'audio_name' => 'kunjungan.wav',
            'audio_path' => 'transcriptions/kunjungan.wav',
            'mime_type' => 'audio/wav',
            'file_size' => 100,
            'source_type' => 'upload',
            'text' => 'Teks transkrip asli.',
            'formatted_report' => 'Laporan lama.',
        ]);

        $response = $this->post(route('transcripts.report.regenerate', $transcript));

        $transcript->refresh();
        $this->assertSame('Teks transkrip asli.', $transcript->text);
        $this->assertSame('Laporan lama.', $transcript->formatted_report);
        $response->assertRedirect(route('home', ['active' => $transcript->id]));
        $response->assertSessionHas('report_error');
    }

    public function test_it_displays_active_transcript_and_table_row(): void
    {
        Storage::fake('public');
        $stored = Storage::disk('public')->put('transcriptions/sample.mp3', 'dummy audio bytes');

        $transcript = Transcript::create([
            'title' => 'Rapat Mingguan',
            'audio_name' => 'rapat-mingguan.mp3',
            'audio_path' => 'transcriptions/sample.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 102400,
            'source_type' => 'record',
            'text' => 'Hasil pembahasan rapat mingguan ID-Grow.',
        ]);

        $response = $this->get(route('home', ['active' => $transcript->id]));

        $response->assertStatus(200);
        $response->assertSee('Rapat Mingguan');
        $response->assertSee('Hasil pembahasan rapat mingguan ID-Grow.');
        $response->assertSee($transcript->audio_url);
        $response->assertSee('Riwayat Transkripsi', false);
        $response->assertSee('1 Tersimpan');
    }

    public function test_transcription_settings_can_be_saved_and_uploaded_audio_uses_selected_provider(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->post(route('settings.transcription.update'), [
            'provider' => 'speaches',
            'model' => 'Systran/faster-whisper-small',
        ])->assertRedirect(route('settings.transcription'));
        Storage::disk('local')->assertExists('transcription-settings.json');

        Http::fake(['http://127.0.0.1:9001/v1/audio/transcriptions' => Http::response(['text' => 'Speaches dipakai.', 'segments' => []])]);
        $this->post(route('transcribe'), [
            'audio' => UploadedFile::fake()->create('sample.mp3', 20, 'audio/mpeg'),
            'source_type' => 'upload',
            'provider' => 'speaches',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:9001/v1/audio/transcriptions'
            && str_contains($request->body(), 'Systran/faster-whisper-small')
            && str_contains($request->body(), 'sample.mp3')
            && str_contains($request->body(), 'name="language"'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), ':9000/asr'));
    }

    public function test_speaches_model_can_be_downloaded_explicitly_from_settings(): void
    {
        Storage::fake('local');
        Http::fake(['http://127.0.0.1:9001/v1/models/Systran/faster-whisper-small' => Http::response(['id' => 'Systran/faster-whisper-small'])]);

        $this->post(route('settings.transcription.download-model'), [
            'model' => 'Systran/faster-whisper-small',
        ])->assertRedirect(route('settings.transcription'));

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'http://127.0.0.1:9001/v1/models/Systran/faster-whisper-small');
    }

    public function test_scribecrate_upload_uses_openai_compatible_multipart_fields(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('transcription-settings.json', json_encode([
            'provider' => 'scribecrate',
            'model' => 'Systran/faster-whisper-small',
        ]));
        config(['services.whisper.scribecrate_api_key' => 'test-scribe-key']);
        Http::fake([
            'http://127.0.0.1:9002/v1/audio/transcriptions' => Http::response(['text' => 'ScribeCrate dipakai.', 'segments' => []]),
        ]);

        $this->post(route('transcribe'), [
            'audio' => UploadedFile::fake()->create('sample.mp3', 20, 'audio/mpeg'),
            'source_type' => 'upload',
        ])->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:9002/v1/audio/transcriptions'
            && str_contains($request->body(), 'name="file"')
            && str_contains($request->body(), 'name="model"')
            && str_contains($request->body(), 'whisper-1')
            && str_contains($request->body(), 'name="language"')
            && str_contains($request->body(), 'sample.mp3')
            && $request->hasHeader('Authorization', 'Bearer test-scribe-key'));
    }

    public function test_whisperlive_can_be_selected_for_file_transcription(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('transcription-settings.json', json_encode(['provider' => 'whisperlive']));
        Http::fake([
            'http://127.0.0.1:9004/v1/audio/transcriptions' => Http::response(['text' => 'WhisperLive REST dipakai.', 'segments' => []]),
        ]);

        $this->post(route('transcribe'), [
            'audio' => UploadedFile::fake()->create('live-test.mp3', 20, 'audio/mpeg'),
            'source_type' => 'upload',
            'provider' => 'whisperlive',
        ])->assertRedirect();

        $this->assertDatabaseHas('transcripts', [
            'audio_name' => 'live-test.mp3',
            'transcription_provider' => 'whisperlive',
        ]);
        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:9004/v1/audio/transcriptions'
            && str_contains($request->body(), 'name="file"')
            && str_contains($request->body(), 'name="model"')
            && str_contains($request->body(), 'whisper-1')
            && str_contains($request->body(), 'live-test.mp3'));
    }

    public function test_it_can_download_transcript_as_text_file(): void
    {
        $transcript = Transcript::create([
            'title' => 'Catatan Proyek',
            'audio_name' => 'catatan.wav',
            'audio_path' => 'transcriptions/catatan.wav',
            'mime_type' => 'audio/wav',
            'file_size' => 50000,
            'source_type' => 'upload',
            'text' => 'Catatan audio proyek yang akan diunduh.',
        ]);

        $response = $this->get(route('transcripts.download', $transcript));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="catatan-proyek-'.$transcript->id.'.txt"');
        $response->assertSee('Catatan audio proyek yang akan diunduh.');
    }

    public function test_it_deletes_transcript_and_audio_file(): void
    {
        Storage::fake('public');
        $filePath = 'transcriptions/todelete.mp3';
        Storage::disk('public')->put($filePath, 'dummy audio data');

        $transcript = Transcript::create([
            'title' => 'File Hapus',
            'audio_name' => 'todelete.mp3',
            'audio_path' => $filePath,
            'mime_type' => 'audio/mpeg',
            'file_size' => 1234,
            'source_type' => 'upload',
            'text' => 'Teks yang akan dihapus.',
        ]);

        Storage::disk('public')->assertExists($filePath);

        $response = $this->delete(route('transcripts.destroy', $transcript));

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('transcripts', ['id' => $transcript->id]);
        Storage::disk('public')->assertMissing($filePath);
    }
}
