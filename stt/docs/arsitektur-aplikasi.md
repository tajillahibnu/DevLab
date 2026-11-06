# Pola arsitektur aplikasi STT

Dokumen ini menjadi acuan saat menambah fitur ke aplikasi STT. Polanya ditulis ulang dari pembacaan kode proyek `prima`; tidak ada kode prima yang disalin. Backend mengikuti modul Tipe B di `modules/`, dengan pemisahan layer per sub-alur.

## Ruang lingkup

Acuan ini membahas backend Laravel, API, data, konfigurasi, dan pengujian. Autentikasi dan login belum termasuk. Jangan menambahkan middleware atau tabel auth sebagai bagian dari pola fitur sebelum kebutuhan itu dibahas.

STT saat ini memakai Laravel 13, PHP 8.3, SQLite, Blade, Vite, dan `laravel/ai`. Struktur backend mengikuti pola modul Tipe B seperti di prima (lihat bagian Organisasi kode), yaitu modul terpadu per siklus fitur di bawah `modules/`.

## Prinsip kerja

- Kelompokkan kode backend di bawah `modules/` dengan pola Tipe B: root modul untuk komponen bersama, sub-alur untuk tanggung jawab fungsional.
- Controller menangani HTTP: menerima request, memanggil use-case, lalu mengembalikan response.
- Aturan bisnis dan transaksi database berada di Action/use-case, bukan di controller.
- Pakai fitur Laravel langsung. Buat Service hanya untuk logika yang dipakai lintas fitur; buat Repository hanya jika ada kebutuhan nyata untuk memisahkan akses data.
- Jangan menyalin pola lama atau kompleksitas prima secara otomatis. Perbaiki file yang sedang disentuh, hindari refactor massal.

## Alur endpoint

```text
Route
  -> FormRequest (bila validasi cukup kompleks atau digunakan ulang)
  -> Controller
  -> Action/use-case (bila ada aturan bisnis atau beberapa langkah)
  -> Model/database
  -> Resource (bila perlu membentuk kontrak JSON)
  -> Response
```

Untuk endpoint kecil, validasi Laravel di controller masih dapat diterima. Pindahkan validasi ke FormRequest saat rule berkembang, perlu pesan/atribut khusus, atau dipakai oleh beberapa alur. Controller tipis, tetapi tidak perlu membuat Action untuk satu pemanggilan sederhana tanpa logika bisnis.

## Organisasi kode

Acuan struktur diambil dari direktori `prima/static/server/modules/`. Di prima, modul dikelompokkan per domain di bawah `modules/` dengan namespace PSR-4 `Modules\` yang didaftarkan pada `autoload` `composer.json`. STT memakai Tipe B sebagai standar: modul terpadu per siklus fitur dengan komponen fungsional dipisah per sub-alur.

### Struktur standar: modul terpadu (Tipe B)

Untuk satu siklus fitur yang memiliki beberapa sub-alur atau menu, root modul memegang model dan komponen bersama, lalu komponen fungsional dipisah per menu/sub-siklus:

```text
modules/NamaFitur/
├── Models/          # Model bersama unit modul
├── Shared/          # Adapter lintas modul
├── Resources/       # Resource JSON bersama
├── Enums/           # Status dan konstanta domain
├── Events/          # Event domain yang dilepas modul
├── NamaSubAlur1/    # Contoh: Transcribe/
│   ├── Controllers/ # Tipis: validasi -> panggil Action -> response
│   ├── Requests/    # Rule validasi FormRequest
│   ├── Actions/     # Satu use-case = satu class; transaksi DB di sini
│   └── Services/    # Hanya logika lintas modul; kosongkan bila tidak perlu
├── NamaSubAlur2/    # Contoh: Dashboard/
│   ├── Controllers/
│   ├── Requests/
│   ├── Actions/
│   └── Services/
└── Routes/
    ├── api.php      # Entry utama: prefix + middleware + auto-load route sub-fitur
    └── api/         # Satu entitas/sub-alur = satu file route
```

Pemakaian nyata di prima: `modules/Inventaris/` memegang `Models/`, `Shared/`, `Resources/`, dan `Enums/` di root, lalu memisahkan `Distribusi/`, `Stok/`, `Pengadaan/`, `Permintaan/` sebagai sub-alur yang masing-masing memisahkan `Controllers/`, `Actions/`, dan `Routes/` sendiri.

### Penerapan pada STT

Buat modul baru dengan pola Tipe B sejak awal, mengikuti langkah berikut:

1. Daftarkan mapping `"Modules\\": "modules/"` pada `autoload` `composer.json`, lalu jalankan `composer dump-autoload`.
2. Buat `modules/NamaFitur/` dengan `Models/`, `Enums/`, dan `Resources/` di root, lalu sub-alur di bawahnya.
3. Letakkan route tiap sub-alur di `Routes/api/<nama-sub-alur>.php` dan muatkan semuanya dari `Routes/api.php` pusat, agar route tercakup tanpa mendaftarkan satu per satu secara manual.
4. Gunakan namespace `Modules\NamaFitur\SubAlur\...` sesuai lokasi file.

Untuk fitur yang sangat kecil dan belum punya sub-alur, satu sub-alur saja sudah cukup. Jangan buat folder yang kosong hanya untuk menyeragamkan bentuk. Nama class mengikuti tujuan: `TranscribeAudioAction`, `StoreTranscriptionRequest`, dan `TranscriptionResource`.

## Tanggung jawab layer

### Route

Daftarkan endpoint pada `modules/NamaFitur/Routes/api.php` (entry pusat modul) dan `Routes/api/<nama-sub-alur>.php` per sub-alur. Route hanya menentukan URL, HTTP method, controller, dan middleware yang memang diperlukan. Hindari menempatkan aturan bisnis di closure route.

### Controller

Controller menerjemahkan HTTP ke pemanggilan aplikasi. Ia boleh membaca input tervalidasi dan menentukan status/format response. Pindahkan transaksi, query berantai, serta orkestrasi beberapa langkah ke Action.

Contoh bentuk endpoint:

```php
public function store(StoreTranscriptionRequest $request, StoreTranscriptionAction $action)
{
    $transcription = $action->execute($request->validated());

    return new TranscriptionResource($transcription);
}
```

Sesuaikan bentuk response dengan kontrak endpoint STT. Contoh ini tidak menetapkan envelope JSON baru.

### FormRequest

Gunakan FormRequest untuk validasi input yang memiliki beberapa rule, pesan khusus, atau dipakai berulang. Ambil data dari `$request->validated()`, bukan seluruh payload mentah.

Tanpa autentikasi, `authorize()` dapat mengembalikan `true` untuk request yang memang belum memerlukan otorisasi. Saat otorisasi ditambahkan kelak, aturan akses ditempatkan di middleware/policy/FormRequest sesuai cakupannya, bukan dicampur ke validasi format input.

### Action/use-case

Satu Action menjalankan satu tujuan yang jelas. Terima data tervalidasi atau model yang dibutuhkan, lakukan aturan bisnis dan query, lalu kembalikan model atau nilai hasil. Action tidak membentuk HTTP response.

Gunakan `DB::transaction()` bila beberapa perubahan data harus berhasil atau gagal sebagai satu kesatuan. Suntikkan dependency melalui constructor atau parameter method agar mudah diuji; hindari membuat dependency dengan `new` di tengah alur bisnis.

### Model dan Resource

Model menampung relasi, cast, scope, dan akses data melalui Eloquent. Jangan jadikan model tempat validasi request atau pembentukan response HTTP.

Gunakan API Resource ketika nama/format field keluaran perlu berbeda dari kolom model, ada tanggal/mata uang yang perlu diformat, atau relasi perlu disajikan secara terkendali. Response sederhana boleh memakai model sesuai kontrak API.

### Service

Buat Service jika logika benar-benar dipakai oleh beberapa Action/fitur. Jika hanya satu use-case yang menggunakannya, letakkan logika pada Action. Hindari kelas `Manager` atau Service serba guna yang menampung operasi tak terkait.

## API dan error

Tetapkan kontrak JSON secara konsisten saat endpoint API bertambah: bentuk sukses, bentuk error, status HTTP, dan bentuk error validasi. Gunakan mekanisme Laravel untuk validasi dan exception. Hindari `try/catch` yang mengulang format error di setiap controller.

Jika format error khusus diperlukan, pusatkan penanganannya di konfigurasi exception Laravel dan uji responsnya. Jangan membuat envelope baru tanpa menyesuaikan klien API yang sudah ada.

## Database dan konfigurasi

- Perubahan skema dilakukan melalui migration, bukan perubahan manual yang tidak tercatat.
- Tambahkan foreign key, unique constraint, dan index sesuai aturan data yang benar-benar berlaku.
- Gunakan transaksi untuk operasi multi-tabel yang atomik.
- SQLite adalah konfigurasi default STT saat ini; tes memakai SQLite in-memory. Periksa dukungan SQL pada database target jika konfigurasi deployment berubah.
- Simpan rahasia dan nilai lingkungan di `.env`; dokumentasikan nama variabel yang diperlukan dalam `.env.example` tanpa memasukkan nilai rahasia.

## Pengujian

Tes feature untuk alur HTTP: input valid, input tidak valid, status/response, serta perubahan data yang diharapkan. Tes unit untuk aturan Action yang dapat diuji tanpa seluruh alur HTTP. Gunakan database tes yang dikonfigurasi proyek.

Jangan hanya menguji bahwa halaman dapat dibuka. Untuk fitur agent/transkripsi, uji juga batas integrasi eksternal dengan dependency yang dapat dipalsukan atau di-mock, agar tes tidak bergantung pada layanan model atau jaringan.

## Frontend dan integrasi API

STT saat ini menggunakan Blade dan Vite. Pertahankan pola itu sampai kebutuhan UI menuntut perubahan. Bila frontend mulai melakukan banyak request API, pusatkan konfigurasi request dan penanganan error dalam satu client; hindari menyebar base URL dan aturan error ke banyak komponen.

## Checklist fitur baru

- [ ] Route diletakkan pada file route sub-alur di bawah `modules/` dan tidak memuat logika bisnis.
- [ ] Input divalidasi; data Action berasal dari payload tervalidasi.
- [ ] Controller mengurus HTTP, Action mengurus use-case dan transaksi.
- [ ] Response dan error mengikuti kontrak API yang sama.
- [ ] Migration mencatat perubahan skema dan constraint yang diperlukan.
- [ ] Tes mencakup hasil yang terlihat dan efek persistensi.
- [ ] Tidak ada login/auth yang ditambahkan tanpa keputusan kebutuhan tersendiri.
- [ ] Struktur baru hanya dibuat bila membantu memisahkan tanggung jawab fitur.

## Dasar pembacaan

Pola disusun dari struktur modular Laravel di prima, terutama route per fitur, FormRequest, Action untuk use-case/transaksi, Resource untuk kontrak JSON, dan penanganan exception terpusat. Kode prima juga memiliki variasi dan kasus kompatibilitas yang tidak dijadikan standar STT. Dokumen ini merupakan adaptasi untuk aplikasi STT yang masih kecil, bukan salinan konfigurasi atau implementasi prima.
