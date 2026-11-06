import { defineStore } from 'pinia';
import api, { ensureCsrfCookie } from '../../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({ user: null, checked: false }),
    actions: {
        async loadUser() {
            if (this.checked) return;
            try {
                this.user = (await api.get('/user')).data;
            } catch {
                this.user = null;
            } finally {
                this.checked = true;
            }
        },
        async login(credentials) {
            await ensureCsrfCookie();
            await api.post('/login', credentials);
            this.user = (await api.get('/user')).data;
            this.checked = true;
        },
        async logout() {
            try {
                await api.post('/logout');
            } finally {
                this.user = null;
                this.checked = true;
            }
        },
    },
});
