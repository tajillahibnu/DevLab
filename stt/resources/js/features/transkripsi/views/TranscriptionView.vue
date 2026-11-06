<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../auth/store';
import { useTranscriptionStore } from '../store';

const auth = useAuthStore();
const transcriptions = useTranscriptionStore();
const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');
const busy = ref(false);
const submit = async () => {
    error.value = '';
    busy.value = true;
    try { await auth.login({ email: email.value, password: password.value }); await router.replace('/transcriptions'); }
    catch (e) { error.value = e.response?.data?.message || 'Email atau kata sandi tidak sesuai.'; }
    finally { busy.value = false; }
};
const signOut = async () => { await auth.logout(); await router.push('/login'); };
const wordCount = computed(() => (form.value.instructions.trim().match(/\S+/g) || []).length);
const form = ref({ file: null, language: 'id', preset: 'standard', instructions: '', keywords: '', detect_speakers: false });
const fileLabel = computed(() => form.value.file ? `${form.value.file.name} · ${(form.value.file.size / 1048576).toFixed(2)} MB · Siap diunggah` : 'Format audio atau video · Pilih satu file');
const selectFile = (event) => { form.value.file = event.target.files?.[0] || null; };
const dropFile = (event) => { form.value.file = event.dataTransfer.files?.[0] || null; };
const submitUpload = async () => {
    if (!form.value.file) { transcriptions.error = 'Pilih file audio atau video terlebih dahulu.'; return; }
    const payload = new FormData();
    Object.entries(form.value).forEach(([key, value]) => { if (key === 'file' || (key === 'detect_speakers' && value)) payload.append(key, value); else if (key !== 'detect_speakers' && value) payload.append(key, value); });
    try { await transcriptions.upload(payload); form.value.file = null; await transcriptions.load(); }
    catch { /* Pesan kesalahan tersedia di store. */ }
};
onMounted(() => transcriptions.load());
</script>

<template>
<div class="app-shell"><aside class="sidebar">
    <a class="brand" href="/transcriptions" @click.prevent="router.push('/transcriptions')"><span class="brand-mark">d</span><span>ruang<span class="brand-light">dengar</span></span></a>
    <div class="workspace-label">WORKSPACE</div><div class="workspace-switch"><span class="workspace-avatar">R</span><span><strong>Workspace pribadi</strong><small>Ruang kerja</small></span><span class="chevron">⌄</span></div>
    <nav class="side-nav"><RouterLink class="nav-item" to="/transcriptions"><span>✳</span> Transkripsi baru</RouterLink><RouterLink class="nav-item" to="/history"><span>▤</span> Semua rekaman <span class="nav-count">{{ transcriptions.pagination?.total ?? transcriptions.items.length }}</span></RouterLink></nav>
    <div class="side-section">TERBARU</div><div class="side-empty">Rekaman yang Anda proses akan muncul di sini.</div>
    <div class="sidebar-bottom"><div class="help-card"><span class="help-icon">?</span><div><strong>Butuh bantuan?</strong><small>Pelajari cara kerja transkripsi</small></div><span>↗</span></div><div class="profile"><span class="profile-avatar">{{ (auth.user?.name || 'U').slice(0,1).toUpperCase() }}</span><span><strong>{{ auth.user?.name }}</strong><small>{{ auth.user?.email }}</small></span><button class="chevron logout-button" @click="signOut" aria-label="Keluar">Keluar</button></div></div>
</aside>
<main class="main-content"><header class="topbar"><div class="breadcrumb">Workspace <span>/</span> <strong>{{ $route.path === '/history' ? 'Semua rekaman' : 'Transkripsi baru' }}</strong></div><div class="top-actions"><span class="status-dot"></span><span>Sistem siap</span></div></header><div class="content-wrap">
    <section class="page-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span>RUANG TRANSKRIPSI</div><h1>Ubah audio jadi <em>kata-kata.</em></h1><p>Unggah rekaman Anda dan siapkan transkripsi dengan pengaturan yang sesuai.</p></div><div class="heading-art" aria-hidden="true"><span v-for="n in 11" :key="n"></span></div></section>
    <template v-if="$route.path === '/transcriptions'"><div v-if="transcriptions.success" class="notice" role="status">{{ transcriptions.success }}</div><div v-if="transcriptions.error" class="notice error" role="alert">{{ transcriptions.error }}</div>
    <form class="transcription-form" @submit.prevent="submitUpload"><div class="form-main">
        <section class="panel upload-panel"><div class="section-head"><div><div class="step-label"><span>01</span> MULAI DARI FILE</div><h2>Unggah rekaman</h2></div><span class="optional-label">AUDIO / VIDEO</span></div>
            <label class="dropzone" :class="{ dragging: false }" for="audio-file" @dragover.prevent @drop.prevent="dropFile"><input id="audio-file" type="file" accept="audio/*,video/*" @change="selectFile"><span class="upload-icon">↑</span><strong>{{ form.file?.name || 'Tarik file ke sini, atau ' }}<span v-if="!form.file">pilih dari perangkat</span></strong><small>{{ fileLabel }}</small></label>
        </section>
        <section class="panel editor-panel"><div class="section-head"><div><div class="step-label"><span>02</span> HASIL TRANSKRIPSI</div><h2>Ruang transkripsi</h2></div><span class="editor-status"><i></i> Menunggu file</span></div><div class="editor-toolbar"><span class="toolbar-label">DRAF TRANSKRIPSI</span><span class="toolbar-actions">Aa &nbsp; · &nbsp; ↗</span></div><textarea v-model="form.instructions" class="transcript-editor" aria-label="Instruksi transkripsi" placeholder="Tambahkan instruksi khusus untuk membantu menghasilkan transkripsi yang sesuai..."></textarea><div class="editor-footer"><span>Instruksi opsional untuk konteks atau istilah khusus</span><span>{{ wordCount }} kata</span></div></section>
    </div><aside class="settings-column"><section class="panel settings-panel"><div class="settings-title"><div><div class="step-label"><span>03</span> PENGATURAN</div><h2>Sesuaikan hasil</h2></div><span class="sliders">☷</span></div>
        <label class="field-label" for="language">Bahasa audio</label><select id="language" v-model="form.language" class="control"><option value="id">Bahasa Indonesia</option><option value="en">English</option><option value="auto">Deteksi otomatis</option></select><label class="field-label" for="preset">Preset transkripsi</label><select id="preset" v-model="form.preset" class="control"><option value="standard">Standar</option><option value="verbatim">Verbatim</option><option value="clean">Teks rapi</option></select>
        <div class="setting-divider"></div><label class="toggle-row"><span><strong>Deteksi pembicara</strong><small>Tandai pergantian pembicara</small></span><input v-model="form.detect_speakers" type="checkbox"><span class="toggle"></span></label><label class="field-label" for="keywords">Kata kunci <span class="muted">· opsional</span></label><textarea id="keywords" v-model="form.keywords" class="control keyword-control" placeholder="Nama, istilah, atau akronim" rows="3"></textarea><small class="field-hint">Pisahkan kata kunci dengan koma.</small><button class="submit-button" type="submit" :disabled="transcriptions.loading"><span>{{ transcriptions.loading ? 'Memproses…' : 'Mulai transkripsi' }}</span><span>→</span></button><p class="privacy-note">File dikirim untuk diproses sesuai konfigurasi aplikasi.</p>
    </section><div class="process-note"><span class="process-icon">◷</span><div><strong>Status pemrosesan</strong><p>Setelah dikirim, status dan hasil tersedia melalui riwayat rekaman.</p></div></div></aside></form></template>
    <section v-else class="recordings history-page" id="recordings"><div class="recordings-heading"><div><div class="eyebrow"><span class="eyebrow-line"></span>ARSIP WORKSPACE</div><h2>Rekaman terbaru</h2></div><span class="record-count">{{ transcriptions.pagination?.total ?? transcriptions.items.length }} rekaman</span></div><div v-if="transcriptions.loading" class="empty-state" role="status">Memuat riwayat…</div><div v-else-if="transcriptions.error" class="notice error" role="alert">{{ transcriptions.error }}</div><div v-else-if="transcriptions.items.length" class="record-list"><article v-for="record in transcriptions.items" :key="record.id" class="record-row"><span class="record-icon">♫</span><div class="record-info"><strong>{{ record.name || record.filename }}</strong><small>{{ new Date(record.created_at).toLocaleString('id-ID') }}</small><small v-if="record.transcript">{{ record.transcript.slice(0, 180) }}</small><small v-else-if="record.status === 'failed'">Transkripsi gagal diproses.</small></div><span class="record-state">{{ record.status }}</span><span class="record-arrow">↗</span></article></div><div v-else class="empty-state"><div class="empty-illustration">♫</div><strong>Belum ada rekaman</strong><p>Rekaman yang Anda kirim akan tampil di sini. Mulai dengan mengunggah file audio atau video.</p><RouterLink to="/transcriptions">Unggah rekaman pertama ↑</RouterLink></div><div v-if="transcriptions.pagination" class="pagination"><button class="icon-button" :disabled="!transcriptions.pagination.current_page || transcriptions.pagination.current_page <= 1" @click="transcriptions.load(transcriptions.pagination.current_page - 1)">Sebelumnya</button><span>Halaman {{ transcriptions.pagination.current_page }} dari {{ transcriptions.pagination.last_page }}</span><button class="icon-button" :disabled="transcriptions.pagination.current_page >= transcriptions.pagination.last_page" @click="transcriptions.load(transcriptions.pagination.current_page + 1)">Berikutnya</button></div></section>
    <footer class="page-footer"><span>RuangDengar <span class="footer-dot">·</span> Transkripsi audio</span><span>Workspace pribadi</span></footer>
</div></main></div>
</template>
