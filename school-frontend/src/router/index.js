import { createRouter, createWebHistory } from 'vue-router'

/* --- Public Page --- */
import LoginView from '../views/auth/Login.vue'


/* --- Layouts --- */
const AdminLayout = () => import('../layouts/AdminLayout.vue')
const TeacherLayout = () => import('../layouts/TeacherLayout.vue')
const StudentLayout = () => import('../layouts/StudentLayout.vue')

/* --- Admin Pages --- */
const AdminDashboard = () => import('../views/admin/Deshboard.vue')
const ManageUser = () => import('../views/admin/ManageUser.vue')
const ManageTeacher = () => import('../views/admin/ManageTeacher.vue')
const ManageStudent = () => import('../views/admin/ManageStudent.vue')
const ManageYear = () => import('../views/admin/ManageYear.vue')
const ManageClass = () => import('../views/admin/ManageClass.vue')
const ManageSubject = () => import('../views/admin/ManageSubject.vue')
const ManageExam = () => import('../views/admin/ManageExam.vue')
const ManageSchedule = () => import('../views/admin/ManageSchedule.vue')
const ManageAttendance = () => import('../views/admin/ManageAttendance.vue')
const ManageReport = () => import('../views/admin/ManageReport.vue')
// const InputAttendance = () => import('../views/admin/attendance/InputAttendance.vue')
// const ViewAttendance = () => import('../views/admin/attendance/ViewAttendance.vue')


/* --- Teacher Pages --- */
const TeacherDashboard = () => import('../views/teacher/Deshboard.vue')
const Attendance= () => import('../views/teacher/Attendance.vue')
const InputScores = () => import('../views/teacher/InputScores.vue')
const ScheduleTeacher = () => import('../views/teacher/ScheduleTeacher.vue')
const Student = () => import('../views/teacher/Student.vue')
const Report = () => import('../views/teacher/Report.vue')
// const InputAttendanceStudent = () => import('../views/teacher/AttendanceStudent/InputAttendanceStudent.vue')
// const ViewAttendanceStudent = () => import('../views/teacher/AttendanceStudent/ViewAttendanceStudent.vue')


/* --- Student Pages --- */
const StudentHome = () => import('../views/student/Home.vue')
const AttendanceStudent = () => import('../views/student/AttendanceStudent.vue')
const Exam = () => import('../views/student/Exam.vue')
const ScheduleStudent = () => import('../views/student/ScheduleStudent.vue')

const routes = [
  { path: '/', redirect: '/login' },
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { guestOnly: true }
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboard },
      { path: 'user', name: 'Users', component: ManageUser },
      { path: 'teacher', name: 'Teachers', component: ManageTeacher },
      { path: 'student', name: 'Students', component: ManageStudent },
      { path: 'class', name: 'Classes', component: ManageClass },
      { path: 'year', name: 'Years', component: ManageYear },
      { path: 'subject', name: 'Subjects', component: ManageSubject },
      {path: 'attendance', name: 'AdminAttendance', component: ManageAttendance},
      { path: 'exam', name: 'Exams', component: ManageExam },
      { path: 'schedule', name: 'Schedules', component: ManageSchedule },
      {path: 'report', name: 'ManageReport', component: ManageReport}
    ]
  },
  {
    path: '/teacher',
    component: TeacherLayout,
    meta: { requiresAuth: true, role: 'teacher' },
    children: [
      { path: 'dashboard', name: 'TeacherDashboard', component: TeacherDashboard },
      {
        path: 'teacherattendance', name: 'TeacherAttendance', component: Attendance,
      },
      { path: 'inputscores', name: 'InputScores', component: InputScores },
      { path: 'scheduleteacher', name: 'ScheduleTeacher', component: ScheduleTeacher },
      { path: 'student', name: 'Student', component: Student },
      { path: 'report', name: 'Report', component: Report },
      {
        path: 'student/:id/scores',
        name: 'StudentScores',
        component: () => import('../views/teacher/StudentScore.vue'),
        props: true
      },
    ]
  },
  {
    path: '/student',
    component: StudentLayout,
    meta: { requiresAuth: true, role: 'student' },
    children: [
      { path: 'home', name: 'StudentHome', component: StudentHome },
      { path: 'attendance', name: 'AttendanceStudent', component: AttendanceStudent },
      { path: 'exam', name: 'Exam', component: Exam },
      { path: 'schedule', name: 'Schedule', component: ScheduleStudent }
    ]
  },
  { path: '/:pathMatch(.*)*', redirect: '/login' }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

/* --- Route Guard (Security) --- */
router.beforeEach((to, from, next) => {
  // ✅ ប្តូរមកប្រើ localStorage ឱ្យដូចក្នុង authService
  const token = localStorage.getItem('user_token')
  const userRole = localStorage.getItem('user_role')?.toLowerCase()

  // ១. បើទំព័រត្រូវការ Login តែគ្មាន Token
  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  // ២. បើ Login រួចហើយ ព្យាយាមចូលទំព័រ Login វិញ
  if (to.meta.guestOnly && token) {
    if (userRole === 'admin') return next('/admin/dashboard')
    if (userRole === 'teacher') return next('/teacher/dashboard')
    if (userRole === 'student') return next('/student/home')
    return next() // បើ Role មិនច្បាស់លាស់ ឱ្យនៅដដែល
  }

  // ៣. ត្រួតពិនិត្យសិទ្ធិ (Role Check)
  if (to.meta.role && to.meta.role !== userRole) {
    // បើចូលខុស Role ឱ្យទៅទំព័រតាម Role ខ្លួនឯងវិញ
    if (userRole === 'admin') return next('/admin/dashboard')
    if (userRole === 'teacher') return next('/teacher/dashboard')
    if (userRole === 'student') return next('/student/home')

    // បើអត់មាន Role សោះ ទើប Clear ហើយដេញទៅ Login
    localStorage.clear()
    return next('/login')
  }

  next()
})

export default router