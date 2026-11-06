<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store';
const auth = useAuthStore();
const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');
const busy = ref(false);
async function submit() {
    busy.value = true;
    error.value = '';
    try { await auth.login({ email: email.value, password: password.value }); await router.replace('/transcriptions'); }
    catch (e) { error.value = e.response?.data?.message || 'Email atau kata sandi tidak sesuai.'; }
    finally { busy.value = false; }
}
</script>
<template><main class="login-page"><section class="login-panel panel"><a class="brand login-brand" href="/"><span class="brand-mark">d</span><span>ruang<span class="brand-light">dengar</span></span></a><div class="eyebrow"><span class="eyebrow-line"></span>WORKSPACE</div><h1>Selamat datang <em>kembali.</em></h1><p>Masuk menggunakan akun yang dibuat oleh administrator.</p><div v-if="error" class="notice error" role="alert">{{ error }}</div><form @submit.prevent="submit"><label class="field-label" for="email">Email</label><input id="email" v-model="email" class="control" type="email" autocomplete="username" required autofocus><label class="field-label" for="password">Kata sandi</label><input id="password" v-model="password" class="control" type="password" autocomplete="current-password" required><button class="submit-button" type="submit" :disabled="busy">{{ busy ? 'Memeriksa…' : 'Masuk' }} <span>→</span></button></form></section><footer class="page-footer">RuangDengar · Transkripsi audio</footer></main></template>
