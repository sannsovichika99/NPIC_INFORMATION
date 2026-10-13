<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class AcademicGroup extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_academic_group';
    protected $primaryKey = 'id_academic_group';

    protected $casts = [
        'id_academic_calendar' => 'int',
        'id_group' => 'int',
        'id_employee' => 'int',
        'id_batch' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
    ];

    protected $fillable = [
        'id_academic_calendar',
        'id_group',
        'id_employee',
        'id_batch',
        'created_by',
        'updated_by',
    ];

    protected $with = ['group.major.parent.parent'];

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: function () {
                $major = $this->group?->major;                          // Major division
                if (!$major) {
                    return null;
                }

                $parent = $major->parent;                        // Department or Center
                $department = $parent?->label === 'Department' ? $parent : null;
                $faculty = $department?->parent?->label === 'Faculty' ? $department->parent : null;
                $center = $parent?->label === 'Center' ? $parent : null;

                $prefix = $faculty?->short_name ?? $center?->short_name ?? '';
                $deptPart = $department ? $department->short_name . '.' : '';

                return "{$prefix}.{$deptPart}{$major->short_name}.{$this->id_group}";
            }
        );
    }
    protected function IdMainDivision(): Attribute
    {
        return new Attribute(
            get: function () {
                $group = $this->group;
                $center = Center::whereHas('majors', function ($major) use ($group) {
                    $major->where('id_division', $group->id_division);
                })->first();

                $faculty = Faculty::whereHas('departments', function ($department) use ($group) {
                    $department->whereHas('majors', function ($major) use ($group) {
                        $major->where('id_division', $group->id_division);
                    });
                })->first();
                return $faculty ? $faculty->id_division : $center->id_division;
            },
        );
    }

    public function academic_calendar()
    {
        return $this->belongsTo(AcademicCalendar::class, 'id_academic_calendar');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'id_group');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'id_batch');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function student_enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'id_academic_group');
    }

    public function subject_scores()
    {
        return $this->hasMany(SubjectScore::class, 'id_academic_group');
    }

    public function shift()
    {
        return $this->hasOneThrough(Shift::class, Group::class, 'id_group', 'id_shift', 'id_group', 'id_shift');
    }
}
