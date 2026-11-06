<?php

namespace Modules\Transkripsi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transkripsi\Http\Resources\TranscriptionResource;
use Modules\Transkripsi\Models\Transcription;
use Modules\Transkripsi\Requests\StoreTranscriptionRequest;
use Modules\Transkripsi\Services\TranscriptionService;

class TranscriptionController extends Controller
{
    public function index()
    {
        $perPage = max(1, min((int) request('per_page', 10), 50));

        return TranscriptionResource::collection(
            Transcription::query()->where('user_id', request()->user()->id)->latest()->paginate($perPage),
        );
    }

    public function show(Transcription $transcription): TranscriptionResource
    {
        abort_unless($transcription->user_id === request()->user()->id, 404);

        return new TranscriptionResource($transcription);
    }

    public function store(StoreTranscriptionRequest $request, TranscriptionService $service): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('transcriptions', 'local');
        $record = Transcription::create([
            'user_id' => $request->user()->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'filename' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'language' => $request->validated('language'),
            'preset' => $request->validated('preset'),
            'keywords' => filled($request->validated('keywords'))
                ? (array_values(array_filter(array_map('trim', explode(',', $request->validated('keywords'))))) ?: null)
                : null,
            'instructions' => $request->validated('instructions'),
            'detect_speakers' => $request->boolean('detect_speakers'),
            'status' => 'processing',
        ]);

        $service->process($record);
        $record->refresh();

        return response()->json([
            'message' => $record->status === 'completed'
                ? 'Transkripsi berhasil disimpan.'
                : 'Transkripsi gagal diproses. Periksa konfigurasi provider atau coba lagi.',
            'data' => $record,
        ], 201);
    }
}
