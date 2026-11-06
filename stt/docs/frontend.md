# Pola arsitektur frontend

Dokumen ini melengkapi [arsitektur-aplikasi.md](file:///D:/kantor/development/ID-Grow/DevLab/stt/docs/arsitektur-aplikasi.md). Polanya ditulis ulang dari pembacaan kode frontend proyek `prima` (Vue 3 + Pinia + Axios + Vite); tidak ada kode prima yang disalin. Tujuannya menjaga frontend STT tetap rapi saat halaman dan API bertambah.

## Kondisi STT saat ini

STT memakai Blade dengan Vite untuk aset (`resources/js`, `resources/css`). Belum ada SPA, router, atau store. Panduan ini berlaku penuh saat frontend mulai memakai Vue 3 atau halaman interaktif bertambah banyak; untuk halaman Blade sederhana, terapkan bagian "API client tunggal" dan "penamaan" saja.

Autentikasi dan login di luar cakupan. Interceptor token dan guard route yang disebut di sini ditulis sebagai struktur siap pakai, bukan pekerjaan yang dikerjakan sekarang.

## Struktur folder

Satu domain = satu folder di bawah `features/`:

```text
resources/js/                  # Atau src/ bila dipisah dari Laravel
├── api/
│   ├── client.js              # Satu-satunya instance Axios + interceptor
│   └── index.js               # Re-export
├── stores/                    # Store global lintas fitur (ui, notifikasi)
├── composables/               # Composable lintas fitur (useDataList, useModalBus)
├── components/                # Komponen bersama, tidak terikat satu fitur
│   ├── ui/                    # Kit tombol/input/dialog; tiap folder punya index.js
│   └── App*.vue               # AppModal, AppConfirmDialog, LoadingOverlay
├── layouts/                   # Kerangka halaman (sidebar/header/konten)
└── features/
    └── transkripsi/
        ├── views/             # *View.vue; hanya file ini yang dimuat router
        ├── components/        # Komponen internal fitur
        ├── form/              # Modal form (opsional, bila jumlah form besar)
        ├── api/               # transkripsiApi.js
        ├── store/             # useTranskripsiStore (opsional)
        ├── composables/       # useTranskripsi.js (opsional)
        └── routes.js          # export const routes = [...]
```

Folder bersifat opsional. Buat `store/`, `composables/`, atau `routes.js` hanya bila memang ada isinya; jangan buat folder kosong untuk menyeragamkan bentuk.

Tanggung jawab tiap lapis:

- View mengurus tampilan dan memanggil composable/store, bukan mengakses HTTP langsung.
- API module (`features/<fitur>/api/<fitur>Api.js`) berisi objek method verb: `getAll`, `getById`, `store`, `updateStatus`. Method memanggil api client, bukan Axios langsung.
- Store untuk state jangka panjang dan aksi lintas halaman. Composable untuk state sementara halaman (list, loading, filter, auto-refresh).
- Komponen `components/` dipakai lintas fitur. Komponen khusus fitur tetap di dalam folder fiturnya.

## API client tunggal

Seluruh request HTTP lewat satu instance di `api/client.js`. Bentuknya:

```js
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  timeout: 30000
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const message =
      error.response?.data?.message || 'Permintaan gagal. Coba lagi.'
    error.friendlyMessage = message
    return Promise.reject(error)
  }
)

export default api
```

Aturan yang dijaga:

- Base URL memakai `/api` sebagai default karena Laravel dan frontend berjalan same-origin; lewatkan `VITE_API_BASE_URL` hanya bila backend dipisah.
- Setiap error diberi `friendlyMessage`, supaya halaman cukup menampilkan `error.friendlyMessage` tanpa mengurai bentuk response di banyak tempat.
- Penanganan 401/419 (hapus token, redirect) hanya ditambahkan saat login sudah ada. Struktur interceptor sudah siap menampungnya.
- Jangan memanggil `axios.get/post` langsung dari komponen atau store; lewati API module fitur.

## Store dan composable

Pinia dipakai dengan gaya Options (`state`, `getters`, `actions`):

```js
// stores/uiStore.js — global, dipakai lintas fitur
export const useUiStore = defineStore('ui', {
  state: () => ({ pageLoading: false }),
  actions: { showPageLoading() { this.pageLoading = true } }
})
```

Pemisahannya sederhana: state yang hidup lebih lama dari satu halaman (preferensi tampilan, cache menu, status koneksi) masuk store; state yang hidup selama halaman terbuka (list, filter, pagination, loading) masuk composable.

Untuk halaman list, pakai `useDataList` sebagai standar:

```js
const { items, loading, searchQuery, currentPage, totalItems,
        fetchData, loadNextPage, refreshData } = useDataList(
  (params) => transkripsiApi.getAll(params),
  { perPage: 15, debounceMs: 400 }
)
```

`useDataList` menangani fetch, pagination, debounce search, reset halaman saat filter berubah, dan normalisasi meta pagination Laravel (menerima `meta.pagination` maupun `total`/`last_page`). Buat sekali di `composables/`, pakai di semua halaman list.

## Router dan route fitur

Saat frontend memakai Vue Router, daftarkan route per fitur di `routes.js`:

```js
// features/transkripsi/routes.js
export const routes = [
  {
    path: '/transkripsi',
    name: 'transkripsi',
    component: () => import('./views/TranskripsiView.vue'),
    meta: { title: 'Transkripsi' }
  }
]
```

Router global memuat view secara lazy dan `routes.js` secara eager lewat `import.meta.glob`:

```js
const featureViews = import.meta.glob('@/features/**/views/**/*.vue')
const featureRoutes = import.meta.glob('@/features/**/routes.js', { eager: true })
```

Konsekuensi penamaan: file view wajib berakhiran `View.vue` dan berada di `features/<fitur>/views/`, karena glob inilah yang mencocokkan path view ke route. Route statis didaftarkan saat boot; route yang datang dari menu backend didaftarkan saat navigasi pertama, dengan fallback ke halaman "belum tersedia" bila path view tidak ditemukan.

Untuk Blade saat ini, route tetap di `routes/web.php` dan halaman tetap template Blade. Pindah ke pola di atas hanya saat memang ada kebutuhan SPA.

## Penamaan

| Jenis | Pola | Contoh |
|---|---|---|
| View | `*View.vue` di `features/<fitur>/views/` | `TranskripsiView.vue` |
| API module | `<domain>Api.js`, export objek | `transkripsiApi.js` |
| Store | `useXxxStore`, id lowercase | `useUiStore`, `'ui'` |
| Composable | `useXxx.js` | `useTranskripsi.js` |
| Route fitur | `routes.js`, `export const routes` | `features/transkripsi/routes.js` |
| Komponen bersama | prefix `App*` | `AppModal.vue` |
| Komponen fitur | nama deskriptif, tanpa prefix | `TranskripsiDetailModal.vue` |
| Modal form | `XxxFormModal.vue` | `TranskripsiFormModal.vue` |

Import memakai alias `@/` yang menunjuk ke folder sumber frontend.

## Layout

Layout mengurus kerangka halaman: sidebar, header, dan area konten. Kontrol yang dipakai lintas komponen anak disediakan lewat `provide/inject` atau store, bukan prop drilling berlapis. Judul halaman diambil dari `meta` route.

Bila frontend masih Blade, kerangka ini berupa layout Blade (`resources/views/layouts/`) dengan `@yield`/`@section`; pola yang sama berlaku, hanya teknologinya berbeda.

## Yang tidak dipindahkan dari prima

- Build Electron dan packaging desktop.
- Proxy Vite untuk belasan path backend; Laravel dan frontend berjalan same-origin, cukup `/api`.
- Pop-out window dan workspace manager.
- Persistensi ke `localStorage` untuk token/role; belum relevan karena login belum dibahas.

## Checklist fitur frontend

- [ ] Request HTTP hanya lewat `api/client.js` dan API module fitur.
- [ ] View tidak memanggil Axios langsung; data datang dari composable/store.
- [ ] State halaman di composable, state lintas halaman di store.
- [ ] Halaman list memakai `useDataList`.
- [ ] File view berakhiran `View.vue` dan berada di `features/<fitur>/views/`.
- [ ] Route fitur diekspor dari `routes.js` dengan `export const routes`.
- [ ] Error ditampilkan dari `error.friendlyMessage`, bukan parsing response di banyak tempat.
- [ ] Login/auth tidak ditambahkan tanpa keputusan kebutuhan tersendiri.
