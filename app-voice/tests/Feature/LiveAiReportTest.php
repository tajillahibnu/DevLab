<?php

namespace Tests\Feature;

use App\Models\Transcript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji koneksi AI sungguhan melalui controller regenerasi report.
 *
 * Test ini membaca konfigurasi ai-report dari .env dan mengirim request
 * nyata ke endpoint provider. Tidak memakai fake, sehingga keberhasilan
 * membuktikan konfigurasi provider, URL, API key, dan model valid.
 */
class LiveAiReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_regenerate_report_controller_reaches_real_ai_provider(): void
    {
        if (! config('ai-report.model')) {
            $this->markTestSkipped('AI_REPORT_MODEL belum diatur di .env, lewati uji koneksi AI.');
        }

        $this->assertNotEmpty(config('ai-report.url'), 'AI_REPORT_URL belum diatur di .env.');
        $this->assertNotEmpty(config('ai-report.api_key'), 'AI_REPORT_API_KEY belum diatur di .env.');

        $transcript = Transcript::create([
            'title' => 'Uji Koneksi AI',
            'audio_name' => 'uji.wav',
            'audio_path' => 'transcriptions/uji.wav',
            'mime_type' => 'audio/wav',
            'file_size' => 1000,
            'source_type' => 'upload',
            'text' => 'Ini adalah contoh transkrip untuk menguji koneksi provider AI.',
        ]);

        $response = $this->post(route('transcripts.report.regenerate', $transcript));

        $transcript->refresh();

        $response->assertRedirect(route('home', ['active' => $transcript->id]));
        $response->assertSessionHas('report_modal', true);
        $response->assertSessionMissing('report_error');
        $this->assertSame(
            'Ini adalah contoh transkrip untuk menguji koneksi provider AI.',
            $transcript->text,
            'Teks transkrip tidak boleh berubah saat report dibuat ulang.',
        );
        $this->assertNotNull($transcript->formatted_report, 'Report AI tidak tersimpan, koneksi provider kemungkinan gagal.');
        $this->assertNotSame('', trim((string) $transcript->formatted_report), 'Report AI kosong.');
    }
}
