import api from './authService';

const userService = {
    async getUsers(search = '') { // ប្តូរឈ្មោះឱ្យត្រូវជាមួយការហៅក្នុង UI
        const response = await api.get('/users', { params: { search } });
        return response.data;
    },
    async getUserStats() {
        const response = await api.get('/users/stats');
        return response.data;
    },
    async createUser(userData) {
        const response = await api.post('/users', userData);
        return response.data;
    },
    async updateUser(id, userData) {
        const response = await api.put(`/users/${id}`, userData);
        return response.data;
    },
    async deleteUser(id) {
        const response = await api.delete(`/users/${id}`);
        return response.data;
    }
};

// បន្ថែមបន្ទាត់នេះនៅខាងក្រោមគេបង្អស់
export default userService;