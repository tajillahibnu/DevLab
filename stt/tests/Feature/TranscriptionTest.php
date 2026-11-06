<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;
use Modules\Transkripsi\Models\Transcription as TranscriptionRecord;
use Tests\TestCase;

class TranscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', 'http://localhost:8000');
        $this->withHeader('Referer', 'http://localhost:8000/');
    }

    private function loginUser(): User
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'correct-password'])->assertOk();

        return $user;
    }

    public function test_login_success_failure_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'correct-password'])->assertOk()->assertJsonPath('data.email', $user->email);
        $this->getJson('/api/user')->assertOk()->assertJsonPath('email', $user->email);
        $this->postJson('/api/logout')->assertOk();
        auth()->forgetGuards();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_transcription_api_requires_authentication(): void
    {
        $this->getJson('/api/transcriptions')->assertUnauthorized();
        $this->postJson('/api/transcriptions', [])->assertUnauthorized();
    }

    public function test_upload_requires_valid_audio_file_and_options(): void
    {
        $this->loginUser();
        $this->postJson('/api/transcriptions', [])->assertUnprocessable()->assertJsonValidationErrors(['file', 'language', 'preset']);
        $this->postJson('/api/transcriptions', [
            'file' => UploadedFile::fake()->create('document.pdf', 20, 'application/pdf'),
            'language' => 'id',
            'preset' => 'standard',
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_transcription_records_are_scoped_to_the_authenticated_user(): void
    {
        $owner = $this->loginUser();
        Storage::fake('local');
        config([
            'ai.default_for_transcription' => 'openai-compatible',
            'ai.providers.openai-compatible.url' => 'http://localhost:8001/v1',
            'ai.providers.openai-compatible.key' => '',
            'ai.models.transcription.default' => 'whisper-large-v3',
        ]);
        Transcription::fake(['Hasil transkripsi uji']);

        $this->postJson('/api/transcriptions', [
            'file' => UploadedFile::fake()->create('milik-saya.wav', 100, 'audio/wav'),
            'language' => 'id', 'preset' => 'clean',
        ])->assertCreated();

        $record = TranscriptionRecord::firstOrFail();
        $this->assertSame($owner->id, $record->user_id);
        $this->getJson('/api/transcriptions')->assertOk()->assertJsonCount(1, 'data');

        $this->postJson('/api/logout')->assertOk();
        auth()->forgetGuards();
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser, 'web');
        $this->getJson('/api/transcriptions')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/transcriptions/'.$record->id)->assertNotFound();
    }

    public function test_valid_upload_is_saved_privately_and_transcribed_with_fake_sdk(): void
    {
        $this->loginUser();
        Storage::fake('local');
        config([
            'ai.default_for_transcription' => 'openai-compatible',
            'ai.providers.openai-compatible.url' => 'http://localhost:8001/v1',
            'ai.providers.openai-compatible.key' => '',
            'ai.models.transcription.default' => 'whisper-large-v3',
        ]);
        Transcription::fake(['Hasil transkripsi uji']);

        $this->postJson('/api/transcriptions', [
            'file' => UploadedFile::fake()->create('rapat.wav', 100, 'audio/wav'),
            'language' => 'id', 'preset' => 'clean', 'instructions' => 'Gunakan bahasa baku.',
            'keywords' => 'RuangDengar, STT', 'detect_speakers' => '1',
        ])->assertCreated()->assertJsonPath('data.status', 'completed')->assertJsonPath('data.transcript', 'Hasil transkripsi uji');

        $this->assertDatabaseHas('transcriptions', ['filename' => 'rapat.wav', 'status' => 'completed', 'transcript' => 'Hasil transkripsi uji', 'language' => 'id', 'preset' => 'clean', 'detect_speakers' => true, 'user_id' => $this->get('/api/user')->json('id')]);
        $record = TranscriptionRecord::firstOrFail();
        Storage::disk('local')->assertExists($record->path);
        Transcription::assertGenerated(fn ($prompt) => $prompt->isDiarized());
        $this->getJson('/api/transcriptions')->assertOk()->assertJsonPath('data.0.id', $record->id)->assertJsonStructure(['data', 'links', 'meta']);
        $this->getJson('/api/transcriptions/'.$record->id)->assertOk()->assertJsonPath('data.id', $record->id);
    }
}
