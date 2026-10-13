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
use Illuminate\Database\Eloquent\Casts\Attribute;
class Group extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_group';
    protected $primaryKey = 'id_group';

    protected $casts = [
        'id_degree' => 'int',
        'id_division' => 'int',
        'id_grade' => 'int',
        'id_shift' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'group_name',
        'nickname',
        'label',
        'id_degree',
        'id_division',
        'id_grade',
        'id_shift',
        'record_status',
        'group_note',
        'shift_label',
        'created_by',
        'updated_by'
    ];

    protected $with = ['major.parent.parent'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->orderBy('tbl_group.group_name');
        });
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: function () {
                $major = $this->major;                          // Major division
                $parent = $major->parent;                        // Department or Center
                $department = $parent?->label === 'Department' ? $parent : null;
                $faculty = $department?->parent?->label === 'Faculty' ? $department->parent : null;
                $center = $parent?->label === 'Center' ? $parent : null;

                $mainSlug = 'xxx-main-division=' . ($faculty?->id_division ?? $center?->id_division ?? '');
                $departmentSlug = $department?->id_division ? '&xxx-department=' . $department->id_division : '';
                $majorSlug = 'xxx-major=' . ($major?->id_division ?? '');
                $groupSlug = 'xxx-group=' . $this->id_group;

                return "{$mainSlug}{$departmentSlug}&{$majorSlug}&{$groupSlug}";
            }
        );
    }

    protected function IdMainDivision(): Attribute
    {
        return new Attribute(
            get: function () {
                $center = Center::whereHas('majors', function ($major) {
                    $major->where('id_division', $this->id_division);
                })->first();

                $faculty = Faculty::whereHas('departments', function ($department) {
                    $department->whereHas('majors', function ($major) {
                        $major->where('id_division', $this->id_division);
                    });
                })->first();
                return $faculty ? $faculty->id_division : $center->id_division;
            },
        );
    }
    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED')
            ->whereHas('major', function ($q) {
                $q->Enabled();
            });
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED')
            ->orWhereHas('major', function ($q) {
                $q->Disabled();
            });
    }
    public function scopeSchedulable(Builder $query, $id_group, $academic_calendar): void
    {
        $query
            ->where('id_group', $id_group)
            ->where('id_shift', $academic_calendar->id_shift)
            ->Enabled()
            ->whereDoesntHave('join_groups.schedule', function ($q) use ($academic_calendar) {
                $q->AcademicCalendarRule($academic_calendar)
                    ->where('id_academic_calendar', '<>', $academic_calendar->id_academic_calendar);
            });
    }
    public function scopeCrossSchedulable(Builder $query, $id_group, $academic_calendar, $id_user): void
    {
        $group = Group::find($id_group);
        $valid_user = User::ValidPermission2($id_user, 10, $group->id_main_division, $academic_calendar->id_shift)
            ->ValidPermission0($id_user, 11)->first();

        $query->when($valid_user, function ($q) use ($id_group, $academic_calendar) {
            $q->Schedulable($id_group, $academic_calendar);
        })->when(!$valid_user, function ($q) {
            $q->whereRaw('1 = 0'); // Add a condition that will never match any records
        });
    }
    public function scopeSchedulables(Builder $query, $id_division, $academic_calendar): void
    {
        $query
            ->where('id_shift', $academic_calendar->id_shift)
            ->where(function ($q) use ($id_division) {
                $q->whereHas('major', function ($q) use ($id_division) {
                    $q->whereHas('department', function ($q) use ($id_division) {
                        $q->where('id_parent', $id_division);
                    })->orWhere('id_parent', $id_division);
                });
            })
            ->Enabled()
            ->whereDoesntHave('schedules', function ($q) use ($academic_calendar) {
                $q->AcademicCalendarRule($academic_calendar)
                    ->where('id_academic_calendar', '<>', $academic_calendar->id_academic_calendar);
            });
    }
    public function scopeCrossSchedulables(Builder $query, $id_division, $academic_calendar, $id_user): void
    {
        $valid_user = User::ValidPermission2($id_user, 10, $id_division, $academic_calendar->id_shift)
            ->ValidPermission0($id_user, 11)->first();

        $query->when($valid_user, function ($q) use ($id_division, $academic_calendar) {
            $q->Schedulables($id_division, $academic_calendar);
        })->when(!$valid_user, function ($q) {
            $q->whereRaw('1 = 0'); // Add a condition that will never match any records
        });
    }

    public function degree()
    {
        return $this->belongsTo(Degree::class, 'id_degree');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'id_division');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'id_grade');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function join_groups()
    {
        return $this->hasMany(JoinGroup::class, 'id_group');
    }

    public function join_group_histories()
    {
        return $this->hasMany(JoinGroupHistory::class, 'id_group');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'tbl_join_group', 'id_group', 'id_schedule');
    }

    public function academic_groups()
    {
        return $this->hasMany(AcademicGroup::class, 'id_group');
    }
}
