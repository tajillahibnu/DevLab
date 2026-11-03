<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class VisitReportAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
Buat laporan kunjungan dalam Bahasa Indonesia dari transkrip yang diberikan. Pertahankan fakta, nama, angka, dan tindak lanjut yang benar-benar disebut; jangan mengarang informasi. Keluarkan teks biasa yang siap dikirim melalui WhatsApp, tanpa code fence atau pengantar. Format wajib: baris pertama judul tebal WhatsApp (gunakan nama institusi/pihak yang teridentifikasi, atau "Laporan Kunjungan" bila tidak ada), baris kedua dimulai dengan > dan berisi jenis kunjungan bila diketahui, lalu poin-poin utama dan tindak lanjut masing-masing diawali -. Jika informasi tidak tersedia, jangan mengada-adakannya.
PROMPT;
    }
}
