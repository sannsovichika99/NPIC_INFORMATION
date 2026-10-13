<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Employee extends Model
{
    use HasRelationships;
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_employee';

    protected $primaryKey = 'id_employee';

    protected $appends = ['employee_name'];

    protected $casts = [
        'min_hour' => 'int',
        'max_hour' => 'int',
        'id_title' => 'int',
        'id_division' => 'int',
        'id_degree' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
    ];

    protected $fillable = [
        'employee_code',
        'name_en',
        'name_kh',
        'dob',
        'phone',
        'email',
        'address',
        'photo',
        'hired_date',
        'min_hour',
        'max_hour',
        'emp_status',
        'emp_type',
        'emp_role',
        'emp_origin',
        'record_status',
        'act_status',
        'avai_status',
        'id_title',
        'id_division',
        'id_degree',
        'created_by',
        'updated_by',
    ];

    protected $with = ['division', 'title'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->orderBy('tbl_employee.name_en');
        });
    }

    protected function NameEn(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => strtoupper($value),
            set: fn(string $value) => strtoupper($value),
        );
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => "xxx-main-division={$this->division?->id_division}&xxx-employee={$this->id_employee}"
        );
    }

    protected function employeeName(): Attribute
    {
        return Attribute::make(
            get: fn() => ($this->title?->title_en ? $this->title->title_en . '. ' : '') . strtoupper($this->name_en)
        );
    }

    protected function dob(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    protected function hiredDate(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    protected function photo5x5(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/employees/5x5/{$this->photo}") : null
        );
    }

    protected function photo3x4(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/employees/3x4/{$this->photo}") : null
        );
    }

    protected function photo4x6(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/employees/4x6/{$this->photo}") : null
        );
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/employees/thumbnails/{$this->photo}") : null
        );
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED')
            ->whereHas('division', function ($q) {
                $q->Enabled();
            });
    }

    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED')
            ->orWhereHas('division', function ($q) {
                $q->Disabled();
            });
    }

    public function scopeAllowed(Builder $query): void
    {
        $query->where('avai_status', 'ALLOWED');
    }

    public function scopeDenied(Builder $query): void
    {
        $query->where('avai_status', 'DENIED');
    }

    public function scopeSchedulable(Builder $query, $id_employee): void
    {
        $query
            ->where('id_employee', $id_employee)
            ->Enabled();
    }

    public function scopeCrossSchedulable(Builder $query, $id_employee): void
    {
        $query->Schedulable($id_employee)
            ->Allowed();
    }

    public function scopeSchedulables(Builder $query, $id_division): void
    {
        $query
            ->where('id_division', $id_division)
            ->Enabled();
    }

    public function scopeCrossSchedulables(Builder $query, $id_division): void
    {
        $query->Schedulables($id_division)
            ->Allowed();
    }

    public function title()
    {
        return $this->belongsTo(Title::class, 'id_title');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_division');
    }

    public function degree()
    {
        return $this->belongsTo(Degree::class, 'id_degree');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function teaching_hours()
    {
        return $this->hasMany(TeachingHour::class, 'id_employee');
    }

    public function join_teachers()
    {
        return $this->hasMany(JoinTeacher::class, 'id_employee');
    }

    public function join_teacher_histories()
    {
        return $this->hasMany(JoinTeacherHistory::class, 'id_employee');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'tbl_join_teacher', 'id_employee', 'id_schedule');
    }

    public function subjects()
    {
        // // Employee → has many → JoinTeacher → has many → Schedule → belongs to → Subject
        return $this->hasManyDeep(
            Subject::class,
            [JoinTeacher::class, Schedule::class], // Intermediate models, beginning at the far parent (Country).
            [
                'id_employee', // Foreign key on the JoinTeacher table.
                'id_schedule',    // Foreign key on the Schedule table.
                'id_subject',     // Foreign key on the Subject table.
            ],
            [
                'id_employee', // Local key on the Employee table.
                'id_schedule', // Local key on the JoinTeacher table.
                'id_subject',  // Local key on the Schedule table.
            ]
        )->groupBy('tbl_subject.id_subject', 'subject_name', 'tbl_join_teacher.id_employee');
    }

    public function employee_divisions()
    {
        return $this->hasMany(EmployeeDivision::class, 'id_employee');
    }

    public function employee_attrs()
    {
        return $this->hasMany(EmployeeAttr::class, 'id_employee');
    }

    public function isPattrollingAcademicGroup($id_academic_group)
    {
        return AcademicGroup::where('id_academic_group', $id_academic_group)
            ->where('id_employee', $this->id_employee)
            ->exists();
    }

    public function isTeachingAcademicGroup($id_academic_group)
    {
        $academic_group = AcademicGroup::find($id_academic_group);
        if (!$academic_group) {
            return false;
        }
        return Schedule::where('id_academic_calendar', $academic_group->id_academic_calendar)
            ->whereHas('join_teachers', function ($q) {
                $q->where('id_employee', $this->id_employee);
            })
            ->whereHas('join_groups', function ($q) use ($academic_group) {
                $q->where('id_group', $academic_group->id_group);
            })
            ->exists();
    }

    public function isTeachingAcademicGroupInSubject($id_student_group, $id_subject)
    {
        $academic_group = AcademicGroup::find($id_student_group);
        if (!$academic_group) {
            return false;
        }
        return Schedule::where('id_academic_calendar', $academic_group->id_academic_calendar)
            ->where('id_subject', $id_subject)
            ->whereHas('join_teachers', function ($q) {
                $q->where('id_employee', $this->id_employee);
            })
            ->whereHas('join_groups', function ($q) use ($academic_group) {
                $q->where('id_group', $academic_group->id_group);
            })
            ->exists();
    }
}
