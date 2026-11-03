# Audit dan rencana pengembangan AI Agent

Tanggal audit: 3 November 2026  
Lokasi proyek: `D:\kantor\development\ID-Grow\DevLab\app-voice`

## Ringkasan

Aplikasi saat ini menerima audio, mengirimkannya ke Whisper lokal, menyimpan hasil transkrip dan audio, lalu menyediakan riwayat, unduh teks, dan hapus. Skema Laravel AI SDK sudah menyediakan tabel percakapan, tetapi belum ditemukan agent yang digunakan oleh route atau antarmuka.

Rencana tahap pertama menambahkan agent untuk merangkum dan menjawab pertanyaan berdasarkan satu transkrip yang dipilih. Agent tidak menjalankan tool yang mengubah data, tidak menjawab dari kumpulan seluruh transkrip, dan tidak memproses audio langsung. Scope ini menjaga konteks serta akses data tetap terbatas.

## Temuan audit

### Arsitektur dan alur aplikasi

- Proyek menggunakan Laravel `^13.17`, PHP `^8.3`, Laravel AI SDK `^1.3`, Sanctum, dan Tinker. Rinciannya ada di `composer.json`.
- Route web menampilkan halaman, menerima transkripsi, menghapus transkrip, dan mengunduh teks. Route transkrip memakai `TranscribeAudioController`.
- Controller menyimpan audio ke disk `public`, mengirim multipart ke endpoint Whisper `/asr`, menyimpan teks dan segmen ke tabel transcript, lalu menghapus file audio jika proses transkripsi gagal.
- Pemrosesan transkripsi terjadi sinkron dalam request web dengan timeout 300 detik dan batas file 100 MB.
- Model `Transcript` menyediakan data teks, segmen, metadata audio, dan URL audio. Migration transcript menyimpan teks dan segmen transkripsi.
- Migration dari Laravel AI SDK sudah membuat tabel percakapan dan pesan. Pemakaian agent belum ditemukan dalam alur aplikasi yang diperiksa.

### Infrastruktur dan konfigurasi

- `compose.yaml` menjalankan `onerahmet/openai-whisper-asr-webservice` dengan engine `faster_whisper`, model bawaan `small`, CPU, port `9000` dan volume cache model.
- Controller menggunakan HTTP client Laravel untuk `/asr`. URL Whisper dapat berasal dari `WHISPER_URL` atau diturunkan dari URL provider OpenAI.
- `config/ai.php` mendaftarkan beberapa provider AI dan menetapkan OpenAI sebagai default, tetapi daftar provider tidak membuktikan kredensial, model atau layanan telah tersedia.
- Konfigurasi `openai` menunjuk ke port 9000 dengan jalur `/v1`; jalur ini berbeda dari endpoint `/asr` yang dipakai controller dan tidak boleh dianggap sebagai API transkripsi yang tersedia pada image saat ini.
- Ketersediaan container, kredensial provider teks, konfigurasi deployment, serta performa runtime tidak diverifikasi dalam audit ini.

### Fitur dan pengujian

- Antarmuka Blade menyediakan alur rekam/unggah dan menampilkan riwayat transkrip.
- Route yang ditinjau mencakup halaman riwayat, transkripsi, hapus, dan unduh teks.
- Feature test yang ada menguji halaman, transkripsi, unduh, dan hapus; panggilan HTTP dan storage dapat di-fake.
- Belum ada tes untuk agent, percakapan multi-turn, akses lintas pengguna, atau kegagalan provider agent.
- README yang ditemukan masih berupa README kerangka Laravel dan belum mendokumentasikan alur produk.

## Risiko dan keputusan desain

1. Route yang diperiksa tidak menunjukkan middleware autentikasi. Model transcript yang ditinjau juga belum menghubungkan record ke pemilik. Jangan mengaktifkan riwayat percakapan agent untuk data privat lintas pengguna sebelum aturan kepemilikan diputuskan.
2. Audio berada pada disk publik dan model membentuk URL publik. Tentukan kebutuhan privasi dan masa simpan audio sebelum aplikasi dipakai dengan data sensitif.
3. Transkripsi sinkron dapat menahan request sampai 300 detik. Agent tahap pertama juga harus memiliki batas waktu dan batas ukuran konteks.
4. Logika transkripsi saat ini berada di controller. Agent sebaiknya menggunakan kelas agent Laravel AI SDK yang terpisah dari controller dan menjaga controller hanya untuk validasi, otorisasi, orkestrasi request, dan respons.
5. Belum ada mekanisme untuk mengklaim jawaban agent sebagai fakta. UI harus menyatakan bahwa jawaban dibuat dari teks transkrip, dan agent harus menyebutkan bila informasi tidak tersedia di transkrip.

Keputusan scope: rilis awal hanya memakai satu transkrip yang diakses secara sah sebagai konteks. Tidak ada pencarian global, akses web, eksekusi SQL, pengiriman pesan, pengubahan atau penghapusan data, maupun tindakan otomatis.

## Spesifikasi fitur AI agent

### Tujuan dan pengguna

Agent membantu pengguna memahami satu transkrip dengan cepat. Dari halaman detail transkrip, pengguna dapat meminta ringkasan atau mengajukan pertanyaan dalam Bahasa Indonesia.

### Kemampuan rilis awal

- Membuat ringkasan singkat dengan poin pembahasan dan keputusan yang eksplisit disebutkan dalam transkrip.
- Menjawab pertanyaan berdasarkan teks transkrip terpilih.
- Meminta agent menyatakan bahwa jawaban tidak ditemukan bila konteks tidak mendukungnya.
- Menampilkan status proses, jawaban, dan pesan kegagalan yang aman bagi pengguna.
- Mempertahankan percakapan untuk transkrip tersebut jika autentikasi dan kepemilikan data sudah ditetapkan.

### Batasan perilaku

- Konteks setiap permintaan harus memuat hanya transcript ID yang dipilih dan telah lolos otorisasi.
- Jangan mengirim audio ke model agent; gunakan `Transcript.text` yang tersimpan.
- Jangan membuat fakta, nama, tanggal, kutipan, atau keputusan yang tidak ada di transkrip.
- Jangan menyimpan API key di kode atau mengirimkannya ke browser.
- Jangan menambahkan tool yang memiliki efek samping pada rilis awal.
- Jika provider tidak tersedia, tampilkan kegagalan yang dapat ditindaklanjuti tanpa membocorkan stack trace atau kredensial.

### Antarmuka dan endpoint

- Tambahkan panel percakapan pada halaman detail transkrip, dengan aksi ringkasan dan input pertanyaan.
- Endpoint agent menerima transcript ID, pesan pengguna, dan identitas percakapan bila ada. Validasi ukuran pesan dan percakapan.
- Terapkan policy/otorisasi sebelum memuat teks transcript atau percakapan.
- Pesan percakapan harus terikat pada pengguna dan transcript yang sama. Tolak penggunaan conversation ID milik pengguna lain atau transcript berbeda.
- Gunakan respons bertahap (streaming) hanya bila versi Laravel AI SDK dan stack deployment mendukungnya dengan tes. Jika belum, rilis awal boleh memakai respons biasa dengan indikator proses.

### Data dan privasi

- Evaluasi skema conversation bawaan Laravel AI SDK sebelum migrasi baru. Gunakan tabel yang sudah ada bila kolom dan relasinya sesuai.
- Pastikan percakapan menyimpan pemilik, transcript yang menjadi konteks, waktu, role, isi, status, dan metadata usage yang memang diperlukan.
- Tetapkan masa simpan percakapan dan audio. Jangan menambah salinan audio pada riwayat agent.
- Catat metadata operasional tanpa merekam API key atau isi transkrip penuh ke log.

## Tahapan implementasi dan estimasi

Estimasi di bawah adalah rentang kerja rekayasa untuk satu pengembang yang sudah mengenal Laravel. Tidak termasuk waktu menunggu akses provider atau persetujuan kebijakan data.

| Fase | Pekerjaan | Hasil dan gerbang selesai | Estimasi |
|---|---|---|---:|
| 0. Keputusan produk dan akses | Tetapkan siapa pengguna aplikasi, kebutuhan login, kepemilikan transcript, privasi audio, masa simpan, serta provider model teks yang tersedia. Periksa versi SDK terpasang dan dokumentasi paket lokal. | Keputusan tertulis; agent tidak dibuka sebelum batas akses data jelas. | 0,5–1 hari |
| 1. Otorisasi data | Tambahkan relasi pemilik bila multi-pengguna; buat policy untuk membaca transcript dan percakapan; lindungi route sesuai model penggunaan aplikasi. | Tes membuktikan pengguna tidak bisa membaca transcript atau percakapan pengguna lain. | 1–2 hari |
| 2. Agent dan layanan percakapan | Buat kelas agent dengan instruksi berbasis transkrip, validasi konteks, batas panjang, dan penanganan provider; integrasikan penyimpanan percakapan SDK yang sudah tersedia. | Ringkasan dan tanya-jawab satu transkrip berjalan lewat provider yang dikonfigurasi; tidak ada tool tulis. | 1,5–3 hari |
| 3. Antarmuka | Tambahkan panel ringkasan/tanya-jawab di detail transkrip, status loading, riwayat percakapan, empty state, dan pesan error. | Alur dapat digunakan dari browser pada desktop dan layar sempit. | 1–2 hari |
| 4. Keamanan dan pengujian | Tambahkan tes unit/feature, batas input, rate limit yang sesuai, pemeriksaan kebocoran log, serta uji regresi transkripsi/riwayat/hapus/unduh. | Kriteria pengujian di bawah terpenuhi; tidak ada regresi fitur saat ini. | 1–2 hari |
| 5. Uji penerimaan dan rilis | Uji dengan transkrip contoh berizin, cek provider dan konfigurasi lingkungan, siapkan rollback, lalu rilis terbatas. | Pemilik produk menyetujui hasil; konfigurasi provider dan panduan operasi tersedia. | 0,5–1 hari |

Total perkiraan: 4,5–9 hari kerja. Fase 0 dapat mengubah estimasi bila aplikasi perlu autentikasi atau provider model teks belum tersedia.

## Kebutuhan sumber daya

### Perangkat lunak dan layanan

- PHP 8.3 atau versi yang disyaratkan lockfile/runtime proyek, Composer, Node.js/npm untuk build aset, database yang dipakai aplikasi, serta Docker Compose untuk Whisper.
- Laravel AI SDK versi yang telah terkunci di `composer.lock`. Periksa API agent dan conversation pada versi terpasang sebelum menulis implementasi; jangan memperbarui dependensi tanpa persetujuan.
- Satu provider model teks yang mendukung API Laravel AI SDK. Pilih provider dan model setelah memeriksa ketersediaan, batas konteks, harga atau kapasitas lokal, lokasi pemrosesan data, serta kebijakan retensi provider.
- Konfigurasi rahasia di environment deployment atau secret manager. `.env` lokal tidak boleh masuk repository.

### Peran

- Pengembang Laravel untuk agent, otorisasi, penyimpanan percakapan, dan integrasi UI.
- Pemilik aplikasi untuk memutuskan model pengguna, kebijakan audio/transkrip, dan masa simpan.
- Penguji/pemilik produk untuk mengesahkan kualitas jawaban dari set transkrip contoh yang disetujui.

### Anggaran operasional yang harus diukur

Sebelum rilis, ukur latensi p50/p95, token input/output per permintaan, batas konteks, tingkat error provider, dan biaya per 100 permintaan (atau penggunaan CPU/memori jika model lokal). Tetapkan batas anggaran berdasarkan provider dan volume pemakaian aktual; belum ada data pemakaian untuk menentukan angka biaya yang dapat dipertanggungjawabkan.

## Standar pengujian

1. **Unit**: instruksi agent, pembentukan konteks hanya dari transcript terpilih, penanganan transcript kosong/panjang, dan parsing hasil.
2. **Feature**: autentikasi/policy, buat ringkasan, ajukan pertanyaan, percakapan lanjutan, validasi ukuran input, ID transcript tidak ditemukan, percakapan tidak cocok, provider timeout, provider 4xx/5xx, dan respons kosong.
3. **Isolasi provider**: gunakan fake/mock sesuai dukungan SDK untuk seluruh tes otomatis. Tes tidak boleh memerlukan akses jaringan, key nyata, atau biaya model.
4. **Keamanan**: tes IDOR lintas pengguna, manipulasi conversation ID, prompt injection yang meminta akses ke data lain, serta kebocoran secret pada respons dan log.
5. **Regresi**: jalankan semua tes yang sudah ada untuk halaman, transkripsi HTTP Whisper, unduh, dan hapus. Uji route dan migration pada database tes.
6. **Penerimaan manual**: gunakan setidaknya 10 transkrip Bahasa Indonesia yang disetujui, termasuk transkrip pendek, panjang, kosong/noisy, berisi nama/tanggal, dan percakapan dengan keputusan.
7. **Kualitas jawaban**: dua penilai memeriksa kesesuaian terhadap sumber. Jawaban yang tidak didukung harus menyatakan informasi tidak tersedia. Catat hasil per sampel agar ambang bisa diverifikasi.
8. **Operasional**: periksa konfigurasi provider, timeout, batas token/konteks, log tanpa isi sensitif, dan perilaku UI saat layanan tidak tersedia.

## Kriteria keberhasilan terukur

Rilis tahap awal diterima jika seluruh butir berikut terpenuhi:

- 100% tes otomatis lulus, termasuk suite yang sudah ada; tidak ada tes agent yang memanggil provider nyata.
- Semua pengujian akses lintas pengguna lulus: tidak ada pengguna yang dapat membaca transcript atau conversation di luar haknya.
- 100% request agent hanya memuat ID transcript yang diotorisasi; diverifikasi melalui unit/feature test.
- Pada set 10 atau lebih contoh, sedikitnya 90% jawaban faktual dinilai sesuai sumber oleh dua penilai; semua jawaban tanpa bukti harus menyatakan informasi tidak ditemukan.
- Ringkasan memuat poin pembahasan dan keputusan hanya bila memang disebutkan; tidak boleh ada keputusan buatan pada set uji.
- Ukuran pesan dan konteks memiliki batas yang diuji; permintaan di luar batas ditolak dengan respons validasi, bukan menyebabkan request tanpa batas.
- Kegagalan provider ditampilkan sebagai pesan aman dan tercatat sebagai metadata error tanpa API key atau isi transkrip penuh.
- Target latensi penerimaan awal ditetapkan setelah memilih provider. Catat p50 dan p95 pada sedikitnya 30 permintaan; rilis hanya bila p95 memenuhi target yang disetujui pemilik aplikasi dan batas biaya/kapasitas yang ditetapkan.
- Fitur transkripsi, riwayat, unduh, dan hapus tetap lulus uji regresi.

## Di luar scope tahap pertama

- Pencarian dan tanya-jawab lintas seluruh transkrip.
- Ringkasan otomatis saat unggah tanpa tindakan pengguna.
- Diarization atau identifikasi pembicara berbasis AI.
- Integrasi kalender, email, CRM, web search, SQL, atau tool eksternal.
- Agent yang mengubah, menghapus, membagikan, atau mengirim data.
- Klaim kepatuhan atau keamanan provider yang belum diverifikasi.

## Keputusan yang harus disetujui sebelum implementasi

1. Apakah aplikasi satu pengguna lokal atau multi-pengguna yang diekspos melalui jaringan?
2. Apakah diperlukan login? Siapa yang berhak mengakses audio, transkrip, dan percakapan agent?
3. Provider/model teks mana yang akan dipakai, dan bolehkah isi transkrip dikirim ke provider tersebut?
4. Berapa lama audio, transkrip, dan percakapan disimpan? Apakah audio tetap publik atau harus dipindah ke storage privat?
5. Berapa target p95 dan batas biaya/pemakaian bulanan untuk agent?

## Batas audit

Audit ini meninjau berkas sumber dan konfigurasi yang tersedia di direktori proyek. Tes, container, database produksi, kredensial, dan deployment tidak dijalankan atau diverifikasi sebagai bagian dari audit. Temuan yang bergantung pada runtime harus dikonfirmasi sebelum rilis.
