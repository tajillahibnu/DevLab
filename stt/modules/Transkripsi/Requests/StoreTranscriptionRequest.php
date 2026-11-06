<?php

namespace Modules\Transkripsi\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:512000', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/wave,audio/flac,audio/x-flac,audio/ogg,audio/webm,audio/mp4,audio/x-m4a,video/mp4,video/webm,video/quicktime,video/x-matroska'],
            'language' => ['required', 'in:id,en,auto'],
            'preset' => ['required', 'in:standard,verbatim,clean'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'detect_speakers' => ['sometimes', 'boolean'],
        ];
    }
}
