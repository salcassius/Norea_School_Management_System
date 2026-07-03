<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = Exam::query()->with(['year', 'class']);

        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('exam_date', 'asc')->get()
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|string|max:100',
            'year_id'    => 'required|exists:years,id',
            'class_id'   => 'nullable|exists:classes,id',
            // 'subject_id' => 'nullable|exists:subjects,id',
            'exam_date'  => 'required|date',
            'start_time' => 'nullable',
            'end_time'   => 'nullable',
            'status'     => 'nullable|string',
        ]);

        $exam = Exam::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'បានបង្កើតកាលវិភាគប្រឡងដោយជោគជ័យ!',
            'data' => $exam
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $exam = Exam::with(['year', 'class'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $exam]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $exam = Exam::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'type'       => 'sometimes|string',
            'class_id'   => 'nullable|exists:classes,id',
            // 'subject_id' => 'nullable|exists:subjects,id',
            'exam_date'  => 'sometimes|date',
            'start_time' => 'nullable',
            'end_time'   => 'nullable',
            'status'     => 'sometimes|string',
        ]);

        $exam->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលការប្រឡងដោយជោគជ័យ!',
            'data' => $exam
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $exam = Exam::findOrFail($id);
        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបការប្រឡងដោយជោគជ័យ!'
        ]);
    }
}