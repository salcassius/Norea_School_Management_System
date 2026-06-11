<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Exception;

class AttendanceController extends Controller
{
    /**
     * 🎯 មុខងារ៖ ទាញយកតារាងវត្តមានសិស្សតាមថ្នាក់ និងកាលបរិច្ឆេទ
     * GET /api/attendance/sheet
     */
    public function getAttendanceSheet(Request $request)
    {
        try {
            // ១. ផ្ទៀងផ្ទាត់ទិន្នន័យដែលផ្ញើមកពី Frontend
            $request->validate([
                'class_id' => 'required',
                'date'     => 'required|date',
            ]);

            $classId = $request->class_id;
            $date    = $request->date;

            // ២. ទាញយកបញ្ជីឈ្មោះសិស្សនៅក្នុងថ្នាក់រៀននោះ
            try {
                // តម្រៀបតាម ID ឬប្អូនអាចដូរទៅ name_kh វិញក៏បានបើប្រាកដថាមាន Column ហ្នឹង
                $students = Student::where('class_id', $classId)
                    ->orderBy('id', 'asc') 
                    ->get();
            } catch (\Exception $studentError) {
                return response()->json([
                    'success' => false,
                    'message' => 'មានបញ្ហាទាញទិន្នន័យសិស្ស (Table Students)៖ ' . $studentError->getMessage()
                ], 500);
            }

            // ៣. ទាញយកទិន្នន័យវត្តមានដែលធ្លាប់បានកត់ត្រារួចហើយ (បើមាន)
            try {
                $existingAttendance = Attendance::where('class_id', $classId)
                    ->where('date', $date)
                    ->get()
                    ->keyBy('student_id'); 
            } catch (\Exception $attendanceError) {
                return response()->json([
                    'success' => false,
                    'message' => 'មានបញ្ហាទាញទិន្នន័យវត្តមាន (Table Attendances)៖ ' . $attendanceError->getMessage()
                ], 500);
            }
            
            // ៤. ផ្គូរផ្គងសិស្សជាមួយនឹងទិន្នន័យវត្តមានដើម្បីផ្ញើទៅកាន់ Vue.js
            $attendanceSheet = $students->map(function ($student) use ($existingAttendance) {
                $hasRecord = $existingAttendance->has($student->id);
                
                return [
                    'student_id'   => $student->id,
                    'student_name' => $student->name_kh ?? $student->name ?? 'គ្មានឈ្មោះ',
                    'student_code' => $student->student_code ?? $student->student_id_card ?? null,
                    'status'       => $hasRecord ? $existingAttendance[$student->id]->status : 'present',
                    'notes'        => $hasRecord ? $existingAttendance[$student->id]->notes : '',
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $attendanceSheet
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success'       => false,
                'error_message' => $e->getMessage(),
                'line'          => $e->getLine()
            ], 500);
        }
    }

    /**
     * 🎯 មុខងារ៖ រក្សាទុក ឬធ្វើបច្ចុប្បន្នភាពវត្តមានសិស្ស (Bulk Insert/Update)
     * POST /api/attendance/save
     */
    public function saveAttendance(Request $request)
    {
        try {
            // ១. ផ្ទៀងផ្ទាត់រចនាសម្ព័ន្ធ Array ទិន្នន័យវត្តមានដែលផ្ញើមក
            $request->validate([
                'class_id'                => 'required|integer',
                'date'                    => 'required|date',
                'attendance'              => 'required|array',
                'attendance.*.student_id' => 'required|integer',
                'attendance.*.status'     => 'required|in:present,absent,late',
                'attendance.*.notes'      => 'nullable|string',
            ]);

            $classId = $request->class_id;
            $date = $request->date;

            // ២. រត់ Loop ដើម្បីរក្សាទុកទិន្នន័យសិស្សម្នាក់ៗ (បើមាន record រួចហើយវានឹង Update បើអត់ទេវានឹងសរសេរចូលថ្មី)
            foreach ($request->attendance as $record) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'class_id'   => $classId,
                        'date'       => $date,
                    ],
                    [
                        'status' => $record['status'],
                        // 💡 កែសម្រួល៖ បើមកស្រួលបួល (present) មិនបាច់រក្សាទុកចំណាំ (notes) ទេ តែបើ Late/Absent ទើបរក្សាទុក
                        'notes'  => $record['status'] === 'present' ? null : ($record['notes'] ?? null),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'បានកត់ត្រាវត្តមានសិស្សដោយជោគជ័យ! 🎉'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'មិនអាចរក្សាទុកទិន្នន័យវត្តមានបានឡើយ៖ ' . $e->getMessage()
            ], 500);
        }
    }
}