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

        // 🛡️ លក្ខខណ្ឌការពារ៖ ពិនិត្យមើលការជាន់ម៉ោងគ្រូ (ក្នុងឆ្នាំសិក្សាតែមួយ)
        $isTeacherBusy = Schedule::where('year_id', $request->year_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->exists();

        if ($isTeacherBusy) {
            return response()->json([
                'message' => 'លោកគ្រូ/អ្នកគ្រូ រូបនេះមានម៉ោងបង្រៀននៅថ្នាក់ផ្សេងរួចហើយក្នុងម៉ោងនេះ!'
            ], 422);
        }

        // 🛡️ លក្ខខណ្ឌការពារ៖ ពិនិត្យមើលការជាន់ម៉ោងថ្នាក់ (ក្នុងឆ្នាំសិក្សាតែមួយ)
        $isSlotTaken = Schedule::where('year_id', $request->year_id)
            ->where('class_id', $request->class_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->exists();

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

        // 🛡️ ពិនិត្យមើលការជាន់ម៉ោងគ្រូ ដោយមិនគិត ID កំពុងកែខ្លួនឯង
        $isTeacherBusy = Schedule::where('id', '!=', $id)
            ->where('year_id', $schedule->year_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->exists();

        if ($isTeacherBusy) {
            return response()->json([
                'message' => 'លោកគ្រូ/អ្នកគ្រូ រូបនេះមានម៉ោងបង្រៀននៅថ្នាក់ផ្សេងរួចហើយក្នុងម៉ោងនេះ!'
            ], 422);
        }

        // ធ្វើបច្ចុប្បន្នភាព
        $schedule->update($request->all());
        
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
    // ១. យក id របស់ user ដែលកំពុង login (គឺលេខ 4)
    $userId = auth()->id(); 

    // ២. រកមើល record ក្នុងតារាង teachers ដែលមាន user_id = 4 ដើម្បីយក id របស់គ្រូ (គឺលេខ 1)
    $teacher = \App\Models\Teacher::where('user_id', $userId)->first();

    if (!$teacher) {
        return response()->json(['message' => 'មិនមានទិន្នន័យគ្រូទេ'], 404);
    }

    // ៣. ប្រើ $teacher->id (ដែលស្មើនឹង 1) ដើម្បីទាញកាលវិភាគ
    $schedules = Schedule::with(['subject', 'class']) 
        ->where('teacher_id', $teacher->id) 
        ->get();

    return response()->json($schedules);
}

}

