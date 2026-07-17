import api from './authService'

const studentService = {
    async getStudents() {
        const response = await api.get('/students');
        return response.data;
    },

    async createStudent(formData) {
        try {
            const response = await api.post('/students', formData);
            return response.data;
        } catch (error) {
            console.error('Error creating student:', error.response?.data || error);
            throw error;
        }
    },

    async updateStudent(id, formData) {
        try {
            // 💡 បង្កើត FormData ថ្មីមួយដើម្បីសម្អាតទិន្នន័យ ការពារបញ្ហាផ្ញើពាក្យ "null" ឬ "undefined" ជា String
            const cleanedFormData = new FormData();

            if (formData instanceof FormData) {
                for (let [key, value] of formData.entries()) {
                    if (value !== 'null' && value !== 'undefined' && value !== null && value !== undefined && value !== '') {
                        cleanedFormData.append(key, value);
                    }
                }
            } else if (typeof formData === 'object') {
                Object.keys(formData).forEach(key => {
                    const value = formData[key];
                    if (value !== 'null' && value !== 'undefined' && value !== null && value !== undefined && value !== '') {
                        cleanedFormData.append(key, value);
                    }
                });
            }

            cleanedFormData.append('_method', 'PUT');

            const response = await api.post(`/students/${id}`, cleanedFormData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            return response.data;
        } catch (error) {
            console.error('Error updating student:', error.response?.data || error);
            throw error;
        }
    },

    async deleteStudent(id) {
        const response = await api.delete(`/students/${id}`);
        return response.data;
    },

    async getStudentHistory(id) {
        try {
            const response = await api.get(`/students/${id}/history`);
            return response.data;
        } catch (error) {
            console.error('Error fetching student history:', error.response?.data || error);
            throw error;
        }
    }
};

export default studentService;