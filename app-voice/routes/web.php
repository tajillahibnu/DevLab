<?php

use App\Http\Controllers\TranscribeAudioController;
use App\Models\Transcript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('/', function (Request $request) {
    $transcripts = Transcript::latest()->paginate(15);
    $activeId = $request->query('active');
    $activeTranscript = null;

    if ($activeId) {
        $activeTranscript = Transcript::find($activeId);
    }

    if (! $activeTranscript && $transcripts->isNotEmpty()) {
        $activeTranscript = $transcripts->first();
    }

    return view('welcome', compact('transcripts', 'activeTranscript'));
})->name('home');

Route::post('/transcribe', TranscribeAudioController::class)->name('transcribe');
Route::post('/transcripts/{transcript}/report', [TranscribeAudioController::class, 'regenerateReport'])->name('transcripts.report.regenerate');

Route::delete('/transcripts/{transcript}', function (Transcript $transcript) {
    if ($transcript->audio_path && Storage::disk('public')->exists($transcript->audio_path)) {
        Storage::disk('public')->delete($transcript->audio_path);
    }
    $transcript->delete();

    return redirect()->route('home')->with('success', 'Riwayat transkripsi dan file audio berhasil dihapus.');
})->name('transcripts.destroy');

Route::get('/transcripts/{transcript}/download', function (Transcript $transcript) {
    $filename = Str::slug($transcript->title ?: 'transkrip').'-'.$transcript->id.'.txt';
    $content = "=== TRANSKRIP SUARA ===\r\n";
    $content .= 'Judul       : '.($transcript->title ?? '-')."\r\n";
    $content .= 'Sumber      : '.($transcript->source_type === 'record' ? 'Rekaman Suara' : 'Unggah File')."\r\n";
    $content .= 'File Audio  : '.$transcript->audio_name."\r\n";
    $content .= 'Ukuran File : '.$transcript->formatted_size."\r\n";
    $content .= 'Tanggal     : '.$transcript->created_at->format('d/m/Y H:i:s')."\r\n";
    $content .= "==================================================\r\n\r\n";
    $content .= $transcript->text."\r\n";

    return response($content, 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
    ]);
})->name('transcripts.download');
