<?php

namespace Modules\Transkripsi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'language' => $this->language,
            'preset' => $this->preset,
            'keywords' => $this->keywords,
            'instructions' => $this->instructions,
            'detect_speakers' => $this->detect_speakers,
            'status' => $this->status,
            'transcript' => $this->transcript,
            'segments' => $this->segments,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
