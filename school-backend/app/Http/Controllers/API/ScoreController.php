<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoreController extends Controller
{
    /**
     * អនុញ្ញាតតែ Admin និង Teacher
     */
    private function authorizeAccess(): void
    {
        $user = auth()->user();
        $role = strtolower((string) ($user?->role ?? ''));

        if (in_array($role, ['admin', 'teacher'], true)) {
            return;
        }

        abort(403, 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រងពិន្ទុនេះទេ!');
    }

    /**
     * មេគុណមុខវិជ្ជា
     *
     * ខ្មែរ និង គណិត = មេគុណ 2
     * មុខវិជ្ជាផ្សេង = មេគុណ 1
     */
    private function getSubjectCoef(?string $subjectName): int
    {
        $name = trim((string) $subjectName);

        if (
            str_contains($name, 'គណិត') ||
            str_contains($name, 'ខ្មែរ')
        ) {
            return 2;
        }

        return 1;
    }

    /**
     * ពិន្ទុអតិបរមា ត្រូវនឹង Frontend
     *
     * ខ្មែរ និង គណិត = 100
     * មុខវិជ្ជាផ្សេង = 50
     */
    private function getSubjectMaxScore(?string $subjectName): float
    {
        $name = trim((string) $subjectName);

        if (
            str_contains($name, 'គណិត') ||
            str_contains($name, 'ខ្មែរ')
        ) {
            return 100;
        }

        return 50;
    }

    /**
     * និទ្ទេស A - F ដោយផ្អែកលើមធ្យមភាគអតិបរមា 50
     */
    private function calculateGradeKh(float $average): string
    {
        if ($average >= 45) return 'A';
        if ($average >= 40) return 'B';
        if ($average >= 35) return 'C';
        if ($average >= 30) return 'D';
        if ($average >= 25) return 'E';

        return 'F';
    }

    /**
     * មូលវិចារ
     */
    private function calculateRemark(float $average): string
    {
        if ($average >= 45) return 'ល្អប្រសើរ';
        if ($average >= 40) return 'ល្អណាស់';
        if ($average >= 35) return 'ល្អ';
        if ($average >= 30) return 'ល្អបង្គួរ';
        if ($average >= 25) return 'មធ្យម';

        return 'ធ្លាក់';
    }

    /**
     * រក្សាទុក / កែប្រែពិន្ទុ
     */
    public function saveScores(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'exam_id' => ['required'],
            'class_id' => ['required'],
            'scores' => ['required', 'array'],
            'scores.*.student_id' => ['required'],
            'scores.*.scores' => ['nullable', 'array'],
        ]);

        $userId = auth()->id();

        $teacher = Teacher::where('user_id', $userId)->first();
        $teacherId = $teacher?->id;

        $savedCount = 0;
        $deletedCount = 0;
        $skippedCount = 0;

        try {
            DB::transaction(function () use (
                $request,
                $teacherId,
                &$savedCount,
                &$deletedCount,
                &$skippedCount
            ) {
                // ទាញ subjects ទាំងអស់មកសម្រាប់ validate max score
                $subjects = Subject::query()
                    ->select('id', 'name')
                    ->get()
                    ->keyBy('id');

                foreach ($request->input('scores', []) as $studentData) {
                    $studentId = $studentData['student_id'] ?? null;

                    if (!$studentId) {
                        $skippedCount++;
                        continue;
                    }

                    $scoresMap = $studentData['scores']
                        ?? $studentData['subject']
                        ?? [];

                    foreach ($scoresMap as $key => $value) {
                        /*
                         * គាំទ្រទម្រង់ពីរ៖
                         *
                         * scores: { "1": 80, "2": 95 }
                         *
                         * ឬ
                         *
                         * scores: [
                         *   { subject_id: 1, score_value: 80 }
                         * ]
                         */
                        $subjectId = is_array($value)
                            ? ($value['subject_id'] ?? null)
                            : $key;

                        $scoreValue = is_array($value)
                            ? ($value['score_value'] ?? null)
                            : $value;

                        if ($subjectId === null || $subjectId === '') {
                            $skippedCount++;
                            continue;
                        }

                        $subject = $subjects->get($subjectId);

                        if (!$subject) {
                            $skippedCount++;
                            continue;
                        }

                        $matchAttributes = [
                            'exam_id' => $request->exam_id,
                            'class_id' => $request->class_id,
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'type' => 'subject',
                        ];

                        /*
                         * បើ user លុបលេខក្នុង input ទុកទទេ
                         * ត្រូវលុបពិន្ទុចាស់ក្នុង Database ដែរ។
                         */
                        if (
                            $scoreValue === null ||
                            $scoreValue === '' ||
                            !is_numeric($scoreValue)
                        ) {
                            $deleted = Score::where($matchAttributes)->delete();

                            if ($deleted > 0) {
                                $deletedCount += $deleted;
                            }

                            continue;
                        }

                        $maxScore = $this->getSubjectMaxScore($subject->name);

                        // រក្សាពិន្ទុក្នុងចន្លោះ 0 ដល់ max score
                        $cleanScore = max(
                            0,
                            min((float) $scoreValue, $maxScore)
                        );

                        $values = [
                            'score_value' => $cleanScore,
                        ];

                        if ($teacherId !== null) {
                            $values['teacher_id'] = $teacherId;
                        }

                        Score::updateOrCreate($matchAttributes, $values);

                        $savedCount++;
                    }
                }

                /*
                 * គណនាពិន្ទុសរុប មធ្យមភាគ ចំណាត់ថ្នាក់ និទ្ទេស មូលវិចារ
                 */
                $this->recalculateTotals(
                    $request->exam_id,
                    $request->class_id,
                    $teacherId
                );
            });

            return response()->json([
                'message' => 'រក្សាទុកពិន្ទុបានជោគជ័យ!',
                'saved' => $savedCount,
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Score Save Error', [
                'message' => $e->getMessage(),
                'exam_id' => $request->exam_id,
                'class_id' => $request->class_id,
            ]);

            return response()->json([
                'message' => 'មានបញ្ហាក្នុងការរក្សាទុកពិន្ទុ៖ ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ទាញយកពិន្ទុសម្រាប់ InputScore.vue និង ResultScore.vue
     *
     * សំខាន់៖ ទាញតែ type = subject
     * មិនត្រូវបញ្ជូន row type = total ទៅ frontend ទេ
     */
    public function getScores(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'exam_id' => ['required'],
            'class_id' => ['required'],
        ]);

        $scoresByStudent = Score::query()
            ->with('student:id,name_kh,name_en,gender')
            ->where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->where('type', 'subject') // កុំយក total មកបូកស្ទួន
            ->orderBy('student_id')
            ->orderBy('subject_id')
            ->get()
            ->groupBy('student_id');

        $result = [];

        foreach ($scoresByStudent as $studentId => $studentScores) {
            $firstScore = $studentScores->first();
            $student = $firstScore?->student;

            if (!$student) {
                continue;
            }

            $subjectScores = [];

            foreach ($studentScores as $score) {
                // ការពារ subject_id null
                if ($score->subject_id !== null) {
                    $subjectScores[(string) $score->subject_id] = (float) $score->score_value;
                }
            }

            $totalRow = Score::query()
                ->where('exam_id', $request->exam_id)
                ->where('class_id', $request->class_id)
                ->where('student_id', $studentId)
                ->where('type', 'total')
                ->first();

            $result[] = [
                'student_id' => $student->id,

                'student' => [
                    'id' => $student->id,
                    'name_kh' => $student->name_kh,
                    'name_en' => $student->name_en,
                    'gender' => $student->gender,
                ],

                // មានតែ subject scores ប៉ុណ្ណោះ
                'scores' => $subjectScores,

                // ផ្ញើជាព័ត៌មានបន្ថែមបាន ប៉ុន្តែ Vue មិនត្រូវបូកវាចូល scores ទេ
                'total_score' => $totalRow?->score_value,
                'average_score' => $totalRow?->average_score,
                'rank' => $totalRow?->rank,
                'grade_kh' => $totalRow?->grade_kh,
                'remark' => $totalRow?->remark,
            ];
        }

        return response()->json($result);
    }

    /**
     * គណនាសរុប / មធ្យមភាគ / ចំណាត់ថ្នាក់
     *
     * រូបមន្តត្រូវដូច inputscore.vue និង resultscore.vue:
     *
     * average = total_score / total_coefficient
     *
     * ឧទាហរណ៍៖
     * ខ្មែរ 80 + គណិត 90 + វិទ្យា 40 = 210
     * មេគុណ = 2 + 2 + 1 = 5
     * average = 210 / 5 = 42.00
     */
    private function recalculateTotals($examId, $classId, $teacherId = null): void
    {
        /*
         * Frontend គណនាមេគុណតាម subjects ទាំងអស់
         * ដូច្នេះ Backend ត្រូវប្រើ subjects ទាំងអស់ដូចគ្នា។
         */
        $subjects = Subject::query()
            ->select('id', 'name')
            ->get();

        $totalCoefficient = $subjects->sum(function ($subject) {
            return $this->getSubjectCoef($subject->name);
        });

        if ($totalCoefficient <= 0) {
            $totalCoefficient = 1;
        }

        $subjectRows = Score::query()
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('type', 'subject')
            ->get()
            ->groupBy('student_id');

        $studentStats = [];

        foreach ($subjectRows as $studentId => $rows) {
            $rawTotal = $rows->sum(function ($row) {
                return (float) $row->score_value;
            });

            /*
             * សំខាន់:
             * កុំយក score * coefficient
             *
             * ព្រោះ Frontend របស់អ្នកប្រើ:
             * total / totalCoefficient
             */
            $average = round($rawTotal / $totalCoefficient, 2);

            $studentStats[] = [
                'student_id' => $studentId,
                'raw_total' => round($rawTotal, 2),
                'average' => $average,
            ];
        }

        /*
         * ចំណាត់ថ្នាក់ដូច resultscore.vue:
         * 100, 90, 90, 80 => rank 1, 2, 2, 4
         */
        usort($studentStats, function ($a, $b) {
            return $b['raw_total'] <=> $a['raw_total'];
        });

        $position = 0;
        $rank = 0;
        $previousTotal = null;

        foreach ($studentStats as $stat) {
            $position++;

            if ($previousTotal === null || $stat['raw_total'] != $previousTotal) {
                $rank = $position;
                $previousTotal = $stat['raw_total'];
            }

            $average = $stat['average'];

            $matchAttributes = [
                'exam_id' => $examId,
                'class_id' => $classId,
                'student_id' => $stat['student_id'],
                'subject_id' => null,
                'type' => 'total',
            ];

            $values = [
                'score_value' => $stat['raw_total'],
                'average_score' => $average,
                'rank' => $rank,
                'grade_kh' => $this->calculateGradeKh($average),
                'grade_en' => $this->calculateGradeKh($average),
                'remark' => $this->calculateRemark($average),
            ];

            if ($teacherId !== null) {
                $values['teacher_id'] = $teacherId;
            }

            Score::updateOrCreate($matchAttributes, $values);
        }
    }
}