<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * ទាញយកកាលវិភាគទៅតាម ឆ្នាំសិក្សា, ថ្នាក់រៀន និង វេនសិក្សា
     * GET /api/schedules
     */
    public function index(Request $request)
    {
        $request->validate([
            'year_id'  => 'required|integer',
            'class_id' => 'required|integer',
            'shift'    => 'required|string',
        ]);

        // 🟢 ជួសជុល៖ ផ្ទុកតែទំនាក់ទំនង subject និង teacher ដែលចាំបាច់សម្រាប់បង្ហាញលើ UI
        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('year_id', $request->year_id)
            ->where('class_id', $request->class_id)
            ->where('shift', $request->shift)
            ->get();

        return response()->json($schedules);
    }

    /**
     * ✅ Helper៖ ពិនិត្យថាតើគ្រូម្នាក់នេះមានម៉ោងបង្រៀនជាន់គ្នា (ថ្ងៃ + ម៉ោង ដូចគ្នា)
     * នៅក្នុងឆ្នាំសិក្សាតែមួយ ដោយមិនគិតថាថ្នាក់ ឬវេនអ្វី
     * (គ្រូម្នាក់មិនអាចនៅ ២ កន្លែងក្នុងពេលតែមួយបានទេ)
     *
     * @return Schedule|null សំណុំកាលវិភាគចាស់ដែលកំពុងជាន់ម៉ោង (null បើគ្មាន)
     */
    private function findTeacherConflict($yearId, $teacherId, $day, $time, $ignoreScheduleId = null)
    {
        $query = Schedule::with(['class', 'subject'])
            ->where('year_id', $yearId)
            ->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('time', $time);

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->first();
    }

    /**
     * ✅ Helper៖ ពិនិត្យថាតើថ្នាក់រៀននេះមានម៉ោងសិក្សាជាន់គ្នា (ថ្ងៃ + ម៉ោង ដូចគ្នា) រួចហើយឬអត់
     */
    private function findClassSlotConflict($yearId, $classId, $day, $time, $ignoreScheduleId = null)
    {
        $query = Schedule::where('year_id', $yearId)
            ->where('class_id', $classId)
            ->where('day', $day)
            ->where('time', $time);

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->exists();
    }

    /**
     * បង្កើតម៉ោងសិក្សាថ្មី
     * POST /api/schedules
     */
    public function store(Request $request)
    {
        $request->validate([
            'year_id'    => 'required|integer',
            'class_id'   => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'day'        => 'required|string',
            'time'       => 'required|string',
            'shift'      => 'required|string',
            'room'       => 'required|string',
        ]);

        // 🛡️ លក្ខខណ្ឌការពារ៖ ពិនិត្យមើលការជាន់ម៉ោងគ្រូ (ក្នុងឆ្នាំសិក្សាតែមួយ, គ្រប់ថ្នាក់/គ្រប់វេន)
        $conflict = $this->findTeacherConflict(
            $request->year_id,
            $request->teacher_id,
            $request->day,
            $request->time
        );

        if ($conflict) {
            $conflictClassName = $conflict->class
                ? "ថ្នាក់ទី {$conflict->class->grade_level}{$conflict->class->name}"
                : 'ថ្នាក់មួយផ្សេងទៀត';

            return response()->json([
                'message' => "លោកគ្រូ/អ្នកគ្រូ រូបនេះកំពុងបង្រៀន{$conflictClassName} នៅថ្ងៃ {$request->day} ម៉ោង {$request->time} រួចហើយ! សូមជ្រើសរើសគ្រូ ឬម៉ោងផ្សេង។"
            ], 422);
        }

        // 🛡️ លក្ខខណ្ឌការពារ៖ ពិនិត្យមើលការជាន់ម៉ោងថ្នាក់ (ក្នុងឆ្នាំសិក្សាតែមួយ)
        $isSlotTaken = $this->findClassSlotConflict(
            $request->year_id,
            $request->class_id,
            $request->day,
            $request->time
        );

        if ($isSlotTaken) {
            return response()->json([
                'message' => 'ម៉ោងសិក្សានេះត្រូវបានរៀបចំរួចរាល់ហើយសម្រាប់ថ្នាក់នេះ!'
            ], 422);
        }

        // បញ្ចូលទិន្នន័យទៅក្នុង Database
        $schedule = Schedule::create($request->all());

        // 🟢 ជួសជុល៖ ផ្ទុកតែទំនាក់ទំនង subject និង teacher ត្រឡប់ទៅឱ្យ Vue.js វិញ
        $schedule->load(['subject', 'teacher']);

        return response()->json([
            'message' => 'បានបន្ថែមម៉ោងសិក្សាដោយជោគជ័យ',
            'data'    => $schedule
        ], 201);
    }

    /**
     * កែសម្រួលម៉ោងសិក្សា
     * PUT/PATCH /api/schedules/{id}
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $request->validate([
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'day'        => 'required|string',
            'time'       => 'required|string',
            'room'       => 'required|string',
        ]);

        // 🛡️ ពិនិត្យមើលការជាន់ម៉ោងគ្រូ ដោយមិនគិត Record កំពុងកែខ្លួនឯង
        // (ប្រើ year_id ដើម ព្រោះ update មិនផ្លាស់ប្តូរឆ្នាំសិក្សាទេ)
        $conflict = $this->findTeacherConflict(
            $schedule->year_id,
            $request->teacher_id,
            $request->day,
            $request->time,
            $schedule->id
        );

        if ($conflict) {
            $conflictClassName = $conflict->class
                ? "ថ្នាក់ទី {$conflict->class->grade_level}{$conflict->class->name}"
                : 'ថ្នាក់មួយផ្សេងទៀត';

            return response()->json([
                'message' => "លោកគ្រូ/អ្នកគ្រូ រូបនេះកំពុងបង្រៀន{$conflictClassName} នៅថ្ងៃ {$request->day} ម៉ោង {$request->time} រួចហើយ! សូមជ្រើសរើសគ្រូ ឬម៉ោងផ្សេង។"
            ], 422);
        }

        // 🛡️ ពិនិត្យមើលការជាន់ម៉ោងថ្នាក់ ដោយមិនគិត Record កំពុងកែខ្លួនឯង
        // (ករណីអ្នកប្តូរថ្ងៃ/ម៉ោង ទៅត្រូវនឹងកន្លែងណាដែលមានទីតាំងរួចហើយ)
        $isSlotTaken = $this->findClassSlotConflict(
            $schedule->year_id,
            $schedule->class_id,
            $request->day,
            $request->time,
            $schedule->id
        );

        if ($isSlotTaken) {
            return response()->json([
                'message' => 'ម៉ោងសិក្សានេះត្រូវបានរៀបចំរួចរាល់ហើយសម្រាប់ថ្នាក់នេះ!'
            ], 422);
        }

        // ធ្វើបច្ចុប្បន្នភាព (តម្រង់តែ Field ដែលបានផ្ទៀងផ្ទាត់ ដើម្បីជៀសវាង Mass-Assignment ខុសគម្រោង)
        $schedule->update($request->only(['subject_id', 'teacher_id', 'day', 'time', 'room']));

        // 🟢 ជួសជុល៖ ផ្ទុកតែទំនាក់ទំនង subject និង teacher ឡើងវិញ
        $schedule->load(['subject', 'teacher']);

        return response()->json([
            'message' => 'បានកែសម្រួលម៉ោងសិក្សាដោយជោគជ័យ',
            'data'    => $schedule
        ]);
    }

    /**
     * លុបម៉ោងសិក្សា
     * DELETE /api/schedules/{id}
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'message' => 'បានលុបម៉ោងសិក្សាដកចេញពីកាលវិភាគជោគជ័យ'
        ]);
    }

    public function getTeacherSchedule()
    {
        // ១. យក id របស់ user ដែលកំពុង login
        $userId = auth()->id();

        // ២. រកមើល record ក្នុងតារាង teachers ដែលមាន user_id ត្រូវនឹង user កំពុង login
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();

        if (!$teacher) {
            return response()->json(['message' => 'មិនមានទិន្នន័យគ្រូទេ'], 404);
        }

        // ៣. ប្រើ $teacher->id ដើម្បីទាញកាលវិភាគ
        $schedules = Schedule::with(['subject', 'class'])
            ->where('teacher_id', $teacher->id)
            ->get();

        return response()->json($schedules);
    }

}