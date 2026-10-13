<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;

class ScoreType extends Model
{
    protected $table = 'tbl_score_type';
    protected $primaryKey = 'id_score_type';
    public $timestamps = false;

    protected $casts = [
        'default_max_score' => 'float',
        'default_min_score' => 'float'
    ];
    protected $fillable = [
        'type_en',
        'type_kh',
        'default_max_score',
        'default_min_score',
    ];
    public function subject_score_types()
    {
        return $this->hasMany(SubjectScoreType::class, 'id_score_type');
    }
}
