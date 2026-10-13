<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
class AcademicCalendar extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_academic_calendar';
    protected $primaryKey = 'id_academic_calendar';

    protected $casts = [
        'id_academic_year' => 'int',
        'id_shift' => 'int',
        'id_stage' => 'int',
        'id_semester' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'start_date_en',
        'start_date_kh',
        'end_date_en',
        'end_date_kh',
        'signature_date_en',
        'signature_date_kh',
        'record_status',
        'id_academic_year',
        'id_shift',
        'id_stage',
        'id_semester',
        'created_by',
        'updated_by'
    ];

    protected function StartDateEn(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }
    protected function EndDateEn(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }
    protected function SignatureDateEn(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED');
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED');
    }
    public function scopeSchedulable(Builder $query, $id_academic_calendar): void
    {
        $query->where('id_academic_calendar', $id_academic_calendar)
            ->Enabled();
    }
    public function scopeCurrentByShift(Builder $query, $id_shift): void
    {
        $query->where('id_shift', $id_shift)
            ->orderBy('start_date_en', 'desc')
            ->orderBy('end_date_en', 'desc');
    }

    public function scopeCurrentByRoomShift(Builder $query, $id_room, $id_shift): void
    {
        $query->CurrentByShift($id_shift)
            ->whereHas('schedules', function ($q) use ($id_room) {
                $q->where('id_room', $id_room);
            });
    }
    public function scopeCurrentByGroupShift(Builder $query, $id_group, $id_shift): void
    {
        $query->CurrentByShift($id_shift)
            ->whereHas('join_groups', function ($q) use ($id_group) {
                $q->where('id_group', $id_group);
            });
    }
    public function scopeCurrentByEmployeeShift(Builder $query, $id_employee, $id_shift): void
    {
        $query->CurrentByShift($id_shift)
            ->whereHas('join_teachers', function ($q) use ($id_employee) {
                $q->where('id_employee', $id_employee);
            });
    }

    public function scopeCurrentBySubjectShift(Builder $query, $id_subject, $id_shift): void
    {
        $query->CurrentByShift($id_shift)
            ->whereHas('schedules', function ($q) use ($id_subject) {
                $q->where('id_subject', $id_subject);
            });
    }

    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'id_academic_year');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'id_stage');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_academic_calendar');
    }

    public function schedule_histories()
    {
        return $this->hasMany(ScheduleHistory::class, 'id_academic_calendar');
    }

    public function academic_groups()
    {
        return $this->hasMany(AcademicGroup::class, 'id_academic_calendar');
    }

    public function join_groups(): HasManyThrough
    {
        return $this->hasManyThrough(
            JoinGroup::class,
            Schedule::class,
            'id_academic_calendar', // Foreign key on the Schedule table...
            'id_schedule', // Foreign key on the JoinTeacher table...
            'id_academic_calendar', // Local key on the AcademicCalendar table...
            'id_schedule' // Local key on the Schedule table...
        );
    }

    public function join_teachers(): HasManyThrough
    {
        return $this->hasManyThrough(
            JoinTeacher::class,
            Schedule::class,
            'id_academic_calendar', // Foreign key on the Schedule table...
            'id_schedule', // Foreign key on the JoinTeacher table...
            'id_academic_calendar', // Local key on the AcademicCalendar table...
            'id_schedule' // Local key on the Schedule table...
        );
    }

    public function session_details(): HasManyThrough
    {
        return $this->hasManyThrough(
            SessionDetail::class,
            Shift::class,
            'id_shift', // Foreign key on the Schedule table...
            'id_shift', // Foreign key on the JoinTeacher table...
            'id_shift', // Local key on the AcademicCalendar table...
            'id_shift' // Local key on the Schedule table...
        );
    }
}
