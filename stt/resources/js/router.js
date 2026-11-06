import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './features/auth/store';
import LoginView from './features/auth/views/LoginView.vue';
import TranscriptionView from './features/transkripsi/views/TranscriptionView.vue';
import HistoryView from './features/transkripsi/views/HistoryView.vue';

const featureRoutes = [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    { path: '/', redirect: '/transcriptions' },
    { path: '/transcriptions', name: 'transcriptions', component: TranscriptionView, meta: { auth: true } },
    { path: '/history', name: 'history', component: HistoryView, meta: { auth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes: [...featureRoutes, { path: '/:pathMatch(.*)*', redirect: '/transcriptions' }],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    if (to.meta.auth) {
        await auth.loadUser();
        if (!auth.user) return { name: 'login' };
    }
    if (to.meta.guest) {
        await auth.loadUser();
        if (auth.user) return { name: 'transcriptions' };
    }
});

export default router;
