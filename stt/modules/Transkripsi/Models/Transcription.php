<?php

namespace Modules\Transkripsi\Models;

use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    protected $fillable = [
        'user_id', 'name', 'filename', 'disk', 'path', 'mime_type', 'size', 'language', 'preset',
        'keywords', 'instructions', 'detect_speakers', 'status', 'transcript', 'segments', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'segments' => 'array',
            'detect_speakers' => 'boolean',
        ];
    }
}
