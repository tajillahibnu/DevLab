<?php

namespace Modules\Transkripsi\Services;

use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;
use Modules\Transkripsi\Models\Transcription as TranscriptionRecord;
use Throwable;

class TranscriptionService
{
    public function process(TranscriptionRecord $record): void
    {
        try {
            $provider = config('ai.providers.'.config('ai.default_for_transcription'));
            if (! filled($provider['url'] ?? null) || (! filled($provider['key'] ?? null) && config('ai.default_for_transcription') !== 'openai-compatible') || ! filled(config('ai.models.transcription.default'))) {
                throw new \RuntimeException('Provider transkripsi belum dikonfigurasi.');
            }

            $language = $record->language === 'auto' ? null : $record->language;
            $pending = Transcription::fromStorage($record->path, $record->disk)
                ->diarize($record->detect_speakers);
            if ($language !== null) {
                $pending->language($language);
            }

            $options = array_filter([
                'prompt' => $this->prompt($record),
            ], fn ($value) => $value !== null);
            if ($options) {
                $pending->withProviderOptions($options);
            }

            $response = $pending->generate(
                config('ai.default_for_transcription'),
                config('ai.models.transcription.default')
            );

            $record->update([
                'status' => 'completed',
                'transcript' => (string) $response,
                'segments' => $response->segments->map(fn ($segment) => $segment->toArray())->values()->all(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $record->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }

    private function prompt(TranscriptionRecord $record): ?string
    {
        $parts = array_filter([
            $record->instructions,
            $record->keywords ? 'Keywords: '.implode(', ', $record->keywords) : null,
            $record->preset === 'verbatim' ? 'Preserve verbatim wording.' : null,
            $record->preset === 'clean' ? 'Return clean, readable text.' : null,
        ]);

        return $parts ? implode("\n", $parts) : null;
    }
}
