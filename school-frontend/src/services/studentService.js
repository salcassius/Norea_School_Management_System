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
                    // ប្រសិនបើតម្លៃជាអក្សរ null, undefined ឬទទេ មិនបាច់ញាត់ចូលទៅក្នុងកញ្ចប់ទិន្នន័យផ្ញើទៅទេ
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

            // បន្ថែមល្បិចបន្លំ Method _method = PUT ចូលទៅក្នុង FormData ស្អាត
            cleanedFormData.append('_method', 'PUT');
            
            // ផ្ញើកញ្ចប់ទិន្នន័យដែលបានសម្អាតរួចទៅកាន់ Laravel
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
    }
};

export default studentService;