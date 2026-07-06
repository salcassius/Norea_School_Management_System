<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['class', 'teacher'])->get();
        
        return response()->json($subjects);
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'name'       => 'required|string|max:255',
        'max_score'  => 'required|integer|min:1',
        'class_id'   => 'nullable|exists:classes,id',
        'teacher_id' => 'nullable|exists:teachers,id',
    ], [
        'name.required'      => 'សូមបញ្ចូលឈ្មោះមុខវិជ្ជា',
        'max_score.required' => 'សូមបញ្ចូលពិន្ទុអតិបរមា',
        'max_score.integer'  => 'ពិន្ទុអតិបរមាត្រូវជាលេខ',
        'class_id.exists'    => 'ថ្នាក់រៀនដែលបានជ្រើសរើសមិនត្រឹមត្រូវ',
        'teacher_id.exists'  => 'គ្រូបង្រៀនដែលបានជ្រើសរើសមិនមានក្នុងប្រព័ន្ធ',
    ]);

    $subject = Subject::create([
        'name'       => $data['name'],
        'max_score'  => $data['max_score'],
        'class_id'   => $data['class_id'] ?? null,
        'teacher_id' => $data['teacher_id'] ?? null,
    ]);

    return response()->json([
        'message' => 'បង្កើតមុខវិជ្ជាបានជោគជ័យ',
        'data' => $subject->load(['teacher', 'class'])
    ], 201);
}


    public function update(Request $request, $id)
{
    $subject = Subject::find($id);

    if (!$subject) {
        return response()->json([
            'message' => 'រកមិនឃើញមុខវិជ្ជាដែលត្រូវកែប្រែឡើយ'
        ], 404);
    }

    $data = $request->validate([
        'name'       => 'required|string|max:255',
        'max_score'  => 'required|integer|min:1',
        'class_id'   => 'nullable|exists:classes,id',
        'teacher_id' => 'nullable|exists:teachers,id',
    ], [
        'name.required'      => 'សូមបញ្ចូលឈ្មោះមុខវិជ្ជា',
        'max_score.required' => 'សូមបញ្ចូលពិន្ទុអតិបរមា',
        'max_score.integer'  => 'ពិន្ទុអតិបរមាត្រូវជាលេខ',
        'class_id.exists'    => 'ថ្នាក់រៀនដែលបានជ្រើសរើសមិនត្រឹមត្រូវ',
        'teacher_id.exists'  => 'គ្រូបង្រៀនដែលបានជ្រើសរើសមិនមានក្នុងប្រព័ន្ធ',
    ]);

    $subject->update([
        'name'       => $data['name'],
        'max_score'  => $data['max_score'],
        'class_id'   => $data['class_id'] ?? null,
        'teacher_id' => $data['teacher_id'] ?? null,
    ]);

    return response()->json([
        'message' => 'កែសម្រួលមុខវិជ្ជាបានជោគជ័យ',
        'data' => $subject->load(['teacher', 'class'])
    ]);
}


    public function destroy($id)
    {
        $subject = Subject::find($id);
        if ($subject) {
            $subject->delete();
            return response()->json(['message' => 'លុបបានជោគជ័យ']);
        }
        return response()->json(['message' => 'រកមិនឃើញមុខវិជ្ជា'], 404);
    }
}