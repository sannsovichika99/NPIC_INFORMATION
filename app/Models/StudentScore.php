<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_student_score';
    protected $primaryKey = 'id_student_score';

    protected $casts = [
        'score' => 'float',
        'id_student_enrollment' => 'int',
        'id_subject_score_type' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];
    protected $fillable = [
        'score',
        'id_student_enrollment',
        'id_subject_score_type',
        'created_by',
        'updated_by',
    ];

    public function student_enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'id_student_enrollment');
    }
    public function subject_score_type()
    {
        return $this->belongsTo(SubjectScoreType::class, 'id_subject_score_type');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
