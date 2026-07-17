import api from './authService'

const teacherService = {
  // ✅ កែសម្រួល៖ ដកពាក្យ export const ចេញ ព្រោះវាស្ថិតក្នុង Object
  async getTeachers() {
    // ប្រាកដថាអ្នកបាន Login ហើយមាន Token ត្រឹមត្រូវ 
    // ព្រោះ Route នេះជាប់ middleware auth:sanctum
    const response = await api.get('/teachers'); 
    return response.data; 
},

  async createTeacher(data) {
    const response = await api.post('/teachers', data);
    return response.data;
  },

  async updateTeacher(id, data) {
    const response = await api.put(`/teachers/${id}`, data);
    return response.data;
  },

  async deleteTeacher(id) {
    const response = await api.delete(`/teachers/${id}`);
    return response.data;
  }
}

export default teacherService;