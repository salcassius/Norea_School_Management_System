<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Score;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

class ScoreController extends Controller
{
    /**
     * មុខងារត្រួតពិនិត្យសិទ្ធិ
     */
    private function authorizeAccess($class_id)
    {
        $user = auth()->user();
        if ($user->role === 'admin' || $user->role === 'teacher') {
            return;
        }
        abort(403, 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រងពិន្ទុក្នុងថ្នាក់នេះទេ!');
    }

    /**
     * រក្សាទុកពិន្ទុ (Bulk Save/Update)
     */
    public function saveScores(Request $request)
    {
        $this->authorizeAccess($request->class_id);

        $request->validate([
            'exam_id'  => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'scores'   => 'required|array', 
        ]);

        $teacher_id = auth()->id();
        $data = [];

        // ទម្រង់ថ្មី៖ $request->scores គឺជា array នៃសិស្សម្នាក់ៗ
        foreach ($request->scores as $studentData) {
            if (!isset($studentData['subject']) || !is_array($studentData['subject'])) continue;

            foreach ($studentData['subject'] as $subject) {
                $data[] = [
                    'exam_id'     => $request->exam_id,
                    'class_id'    => $request->class_id,
                    'student_id'  => $studentData['student_id'],
                    'subject_id'  => $subject['subject_id'] ?? null,
                    'teacher_id'  => $teacher_id,
                    'score_value' => $subject['score_value'] ?? 0,
                    'type'        => 'subject',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        Score::upsert($data, ['exam_id', 'student_id', 'subject_id'], ['score_value', 'updated_at']);
        
        return response()->json(['message' => 'រក្សាទុកពិន្ទុបានជោគជ័យ!']);
    }

    /**
     * ទាញយកពិន្ទុ (សម្រាប់ការបង្ហាញក្នុងតារាង)
     */
    public function getScores(Request $request)
{
    $this->authorizeAccess($request->class_id);

    $request->validate([
        'exam_id' => 'required|exists:exams,id',
        'class_id' => 'required|exists:classes,id',
    ]);

    $students = Student::where('class_id', $request->class_id)
        ->with([
            'scores' => function ($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            }
        ])
        ->get();

    $result = $students->map(function ($student) {

        $scores = [];

        foreach ($student->scores as $score) {
            $scores[$score->subject_id] = $score->score_value;
        }

        return [
            'student_id' => $student->id,
            'student' => [
                'name_kh' => $student->name_kh,
                'gender' => $student->gender,
            ],
            'scores' => $scores
        ];
    });

    return response()->json($result);
}

    /**
     * នាំទិន្នន័យពិន្ទុចេញជា PDF
     */
    public function exportPdf(Request $request, $examId, $classId)
    {
        $this->authorizeAccess($classId);

        $exam = Exam::findOrFail($examId);
        $classRoom = ClassRoom::findOrFail($classId);

        $students = Student::where('class_id', $classId)
            ->with(['scores' => function ($query) use ($examId) {
                $query->where('exam_id', $examId)
                      ->where('type', 'subject')
                      ->with('subject');
            }])->get();

        foreach ($students as $student) {
            $total = $student->scores->sum('score_value');
            $subjectCount = $student->scores->count();
            $average = $subjectCount ? $total / $subjectCount : 0;

            $student->total_score = $total;
            $student->average = round($average, 2);
            $student->grade_kh = $this->calculateGradeKh($average);
            $student->grade_en = $this->calculateGradeEn($average);
            $student->remark = $this->calculateRemark($average);
        }

        $sortedStudents = $students->sortByDesc('total_score')->values();
        foreach ($students as $student) {
            $student->rank = $sortedStudents->search(fn($item) => $item->id === $student->id) + 1;
        }

        $pdf = DomPdf::loadView('pdf.exam-report', compact('students', 'exam', 'classRoom'));
        return $pdf->download("exam-report-class-{$classRoom->name}.pdf");
    }

    private function calculateGradeKh($average)
    {
        if ($average >= 90) return 'ក';
        if ($average >= 80) return 'ខ';
        if ($average >= 70) return 'គ';
        if ($average >= 50) return 'ឃ';
        return 'ង';
    }

    private function calculateGradeEn($average)
    {
        if ($average >= 90) return 'A';
        if ($average >= 80) return 'B';
        if ($average >= 70) return 'C';
        if ($average >= 50) return 'D';
        return 'F';
    }

    private function calculateRemark($average)
    {
        return $average >= 50 ? 'ជាប់ (Pass)' : 'ធ្លាក់ (Fail)';
    }
}