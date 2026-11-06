import { defineStore } from 'pinia';
import { getHistory, uploadTranscription } from '../api/transcriptions';

export const useTranscriptionStore = defineStore('transcriptions', {
    state: () => ({ items: [], pagination: null, loading: false, error: '', success: '' }),
    actions: {
        async load(page = 1) {
            this.loading = true;
            this.error = '';
            try {
                const { data } = await getHistory(page);
                this.items = data.data;
                this.pagination = data.meta;
            } catch (error) { this.error = error.response?.data?.message || 'Riwayat gagal dimuat.'; }
            finally { this.loading = false; }
        },
        async upload(form) {
            this.loading = true;
            this.error = '';
            this.success = '';
            try {
                const { data } = await uploadTranscription(form);
                this.items.unshift(data.data);
                this.success = data.message;
            } catch (error) {
                this.error = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(' ') || 'Unggahan gagal diproses.';
                throw error;
            } finally { this.loading = false; }
        },
    },
});
