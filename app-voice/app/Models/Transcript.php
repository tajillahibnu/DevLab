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
