import api from '../../../api';

export const getHistory = (page = 1) => api.get('/transcriptions', { params: { page } });
export const uploadTranscription = (form) => api.post('/transcriptions', form);
export const getTranscription = (id) => api.get(`/transcriptions/${id}`);
