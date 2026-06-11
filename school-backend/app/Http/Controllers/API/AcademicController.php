<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Year; // ប្រាកដថា Model ឈ្មោះ Year
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    /**
     * ទាញយកបញ្ជីឆ្នាំសិក្សាទាំងអស់
     */
    public function index()
    {
        // តម្រៀបតាម id ពីធំមកតូច ដើម្បីឱ្យឆ្នាំថ្មីនៅខាងលើគេ
        $years = Year::orderBy('id', 'desc')->get();
        return response()->json($years);
    }

    /**
     * កែសម្រួលព័ត៌មានឆ្នាំសិក្សា (PUT/PATCH /api/years/{id})
     */
    public function update(Request $request, $id)
    {
        // ១. ស្វែងរកទិន្នន័យឆ្នាំសិក្សា បើមិនឃើញបោះ 404
        $year = Year::findOrFail($id);

        // ២. ផ្ទៀងផ្ទាត់ទិន្នន័យ (Validation) 
        // ត្រង់ unique:years,year_name,' . $id គឺដើម្បីកុំឱ្យវាទាស់ជាមួយខ្លួនឯងពេល Edit
        $request->validate([
            'year_name'  => 'required|unique:years,year_name,' . $id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // ៣. ប្រសិនបើក្នុង Form គេកំណត់យកជាឆ្នាំសកម្ម (is_active = true)
            // យើងត្រូវបិទ (set false) ឆ្នាំផ្សេងៗទាំងអស់ជាមុនសិន
            if ($request->is_active) {
                Year::query()->update(['is_active' => false]);
            }

            // ៤. ធ្វើបច្ចុប្បន្នភាពទិន្នន័យ
            $year->update([
                'year_name'  => $request->year_name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_active'  => $request->is_active ?? false,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'កែសម្រួលឆ្នាំសិក្សាជោគជ័យ',
                'data' => $year
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'មានបញ្ហាបច្ចេកទេសក្នុងការកែសម្រួល',
                'error' => $e->getMessage() // បង្ហាញ Error ក្នុង Network tab ងាយស្រួលមើល
            ], 500);
        }
    }

    /**
     * បង្កើតឆ្នាំសិក្សាថ្មី
     */
    public function store(Request $request)
    {
        $request->validate([
            'year_name' => 'required|unique:years,year_name',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // ប្រសិនបើ User កំណត់យកឆ្នាំនេះជាឆ្នាំសកម្ម (is_active = true)
        // យើងត្រូវបិទ (set false) ឆ្នាំផ្សេងៗទាំងអស់ជាមុនសិន
        if ($request->is_active) {
            Year::query()->update(['is_active' => false]);
        }

        $year = Year::create([
            'year_name'  => $request->year_name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->is_active ?? false,
        ]);

        return response()->json([
            'message' => 'បង្កើតឆ្នាំសិក្សាជោគជ័យ',
            'data' => $year
        ], 201);
    }

    /**
     * កំណត់ឆ្នាំសិក្សាសកម្ម (Set Active)
     */
    public function setActive($id)
    {
        try {
            DB::beginTransaction();

            // ១. បិទឆ្នាំសិក្សាទាំងអស់ឱ្យទៅជា False
            Year::query()->update(['is_active' => false]);

            // ២. បើកឆ្នាំសិក្សាដែលជ្រើសរើសឱ្យទៅជា True
            $year = Year::findOrFail($id);
            $year->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'message' => 'បានកំណត់ឆ្នាំសិក្សាបច្ចុប្បន្នរួចរាល់',
                'active_year' => $year->year_name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'មានបញ្ហាបច្ចេកទេស'], 500);
        }
    }

    /**
     * លុបឆ្នាំសិក្សា
     */
    public function destroy($id)
    {
        $year = Year::findOrFail($id);
        
        // បន្ថែម Logic ការពារ (Option): មិនអនុញ្ញាតឱ្យលុបឆ្នាំដែលកំពុងសកម្ម
        if ($year->is_active) {
            return response()->json(['message' => 'មិនអាចលុបឆ្នាំសិក្សាដែលកំពុងដំណើរការបានទេ'], 400);
        }

        $year->delete();
        return response()->json(['message' => 'បានលុបឆ្នាំសិក្សាជោគជ័យ']);
    }

    /**
     * មុខងារទាញយក Stats សម្រាប់ Admin Dashboard (បើមានប្រើ)
     */
    public function getStats()
    {
        $stats = [
            'total_years' => Year::count(),
            'active_year' => Year::where('is_active', true)->first()->year_name ?? 'មិនទាន់កំណត់',
        ];
        return response()->json($stats);
    }
}