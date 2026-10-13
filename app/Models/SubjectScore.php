<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubjectScore extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_subject_score';
    protected $primaryKey = 'id_subject_score';

    protected $casts = [
        'max_score' => 'float',
        'min_score' => 'float',
        'id_subject' => 'int',
        'id_academic_group' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];
    protected $fillable = [
        'max_score',
        'min_score',
        'id_subject',
        'id_academic_group',
        'record_status',
        'created_by',
        'updated_by',
    ];

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED');
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }
    public function academic_group()
    {
        return $this->belongsTo(AcademicGroup::class, 'id_academic_group');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function subject_score_types()
    {
        return $this->hasMany(SubjectScoreType::class, 'id_subject_score');
    }
    public function subject_scores()
    {
        return $this->hasManyThrough(
            ScoreType::class,
            SubjectScoreType::class,
            'id_subject_score', // Foreign key on SubjectScoreType table
            'id_score_type', // Foreign key on ScoreType table
            'id_subject_score', // Local key on SubjectScore table
            'id_score_type' // Local key on SubjectScoreType table
        );
    }
}
