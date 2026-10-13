<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;

class SubjectScoreType extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_subject_score_type';
    protected $primaryKey = 'id_subject_score_type';

    protected $casts = [
        'max_score' => 'float',
        'min_score' => 'float',
        'id_subject_score' => 'int',
        'id_score_type' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];
    protected $fillable = [
        'max_score',
        'min_score',
        'id_subject_score',
        'id_score_type',
        'created_by',
        'updated_by',
    ];

    public function subject_score()
    {
        return $this->belongsTo(SubjectScore::class, 'id_subject_score');
    }
    public function score_type()
    {
        return $this->belongsTo(ScoreType::class, 'id_score_type');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function student_scores()
    {
        return $this->hasMany(StudentScore::class, 'id_subject_score_type');
    }
}
