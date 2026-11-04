<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'segments' => 'array',
            'file_size' => 'integer',
            'transcription_duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the accessible public URL for the audio attachment.
     */
    protected function audioUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('storage/'.$this->audio_path)
        );
    }

    protected function formattedTranscriptionDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                $seconds = $this->transcription_duration_seconds;
                if ($seconds === null) {
                    return null;
                }

                if ($seconds < 60) {
                    return $seconds.' detik';
                }

                $minutes = intdiv($seconds, 60);
                $remainingSeconds = $seconds % 60;

                return $remainingSeconds > 0
                    ? $minutes.' menit '.$remainingSeconds.' detik'
                    : $minutes.' menit';
            }
        );
    }

    /**
     * Get a human-readable file size format.
     */
    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->file_size ?: 0;
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 1).' MB';
                }

                return number_format($bytes / 1024, 1).' KB';
            }
        );
    }
}
