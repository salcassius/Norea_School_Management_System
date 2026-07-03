<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    /**
     * ឈ្មោះតារាងនៅក្នុង Database
     */
    protected $table = 'scores';

    /**
     * Columns ដែលអនុញ្ញាតឱ្យបញ្ចូលទិន្នន័យបាន (Mass Assignment)
     */
    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'class_id',
        'teacher_id',
        'score_value',
        'average_score',
        'rank',
        'grade_kh',
        'grade_en',
        'remark',
        'note',
        'type', // 'subject' ឬ 'total'
    ];

    /**
     * បម្លែង DataType ស្វ័យប្រវត្តិនៅពេលទាញយកទិន្នន័យ
     */
    protected $casts = [
        'score_value' => 'float',
        'average_score' => 'float',
        'rank' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES (សម្រាប់ងាយស្រួល Query ត្រងទិន្នន័យ)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope សម្រាប់ទាញយកតែ "ពិន្ទុតាមមុខវិជ្ជា"
     */
    public function scopeSubjects($query)
    {
        return $query->where('type', 'subject');
    }

    /**
     * Scope សម្រាប់ទាញយកតែ "ពិន្ទុសរុប/លទ្ធផលរួម"
     */
    public function scopeTotals($query)
    {
        return $query->where('type', 'total');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS (ទំនាក់ទំនងរវាងតារាង)
    |--------------------------------------------------------------------------
    */

    /**
     * ពិន្ទុនេះជារបស់សិស្ស (Student) ណាខណៈ
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }


    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id'); 
        // សម្គាល់៖ សូមប្តូរ SchoolClass តាមឈ្មោះ Model ថ្នាក់របស់អ្នក
    }

    /**
     * ពិន្ទុនេះស្ថិតក្នុងការប្រឡង (Exam) មួយណា
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * ពិន្ទុនេះជាមុខវិជ្ជា (Subject) អ្វី (អាចជា null បើជា type 'total')
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * ពិន្ទុនេះបញ្ចូលដោយគ្រូ (Teacher) ណា (អាចជា null បើជា type 'total')
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}