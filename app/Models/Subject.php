<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Subject extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_subject';
    protected $primaryKey = 'id_subject';

    protected $casts = [
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'subject_name',
        'subject_code',
        'record_status',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->orderBy('tbl_subject.subject_name');
        });
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED');
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED');
    }
    public function scopeSchedulable(Builder $query, $id_subject, $id_division): void
    {
        $query->where('id_subject', $id_subject)
            ->Enabled();
    }

    public function scopeSchedulables(Builder $query, $id_division): void
    {
        $query->Enabled()
            ->whereHas('division_subjects', function ($q) use ($id_division) {
                $q->where('id_division', $id_division)
                    ->Enabled();
            });
    }

    public function scopeSchedulableInDivisions(Builder $query, $id_subject, $id_divisions): void
    {
        $query
            ->where('id_subject', $id_subject)
            ->Enabled()
            ->whereHas('division_subjects', function ($q) use ($id_divisions) {
                $q->whereIn('id_division', $id_divisions)
                    ->Enabled();
            });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'tbl_division_subject', 'id_subject', 'id_division');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_subject');
    }

    public function schedule_histories()
    {
        return $this->hasMany(ScheduleHistory::class, 'id_subject');
    }

    public function division_subjects()
    {
        return $this->hasMany(DivisionSubject::class, 'id_subject');
    }

    public function subject_scores()
    {
        return $this->hasMany(SubjectScore::class, 'id_subject');
    }
}
