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
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
class ScheduleHistory extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_schedule_history';
    protected $primaryKey = 'id_schedule_history';

    protected $casts = [
        'id_room' => 'int',
        'id_academic_calendar' => 'int',
        'id_subject' => 'int',
        'id_session_detail' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'id_parent' => 'int',
        'deleted_by' => 'int',
        'approved_by' => 'int',
        'approved_at' => 'datetime'
    ];

    protected $fillable = [
        'id_room',
        'id_academic_calendar',
        'id_subject',
        'id_session_detail',
        'from_date',
        'to_date',
        'created_by',
        'updated_by',
        'id_parent',
        'deleted_by',
        'deleted_at',
        'approved_by',
        'approved_at'
    ];

    protected function FromDate(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    protected function ToDate(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    public function scopeAcademicCalendarRule(Builder $query, $academic_calendar): void
    {
        $query->whereHas('academic_calendar', function ($q) use ($academic_calendar) {
            $q
                ->where('start_date_en', '<=', $academic_calendar->getRawOriginal('end_date_en'))
                ->where('end_date_en', '>=', $academic_calendar->getRawOriginal('start_date_en'));
            // ->where('id_shift', $academic_calendar->id_shift);
        })->whereHas('session_detail', function ($q) use ($academic_calendar) {
            $q->whereIn('session_label', $academic_calendar->session_details->pluck('session_label'));
        });
    }

    public function scopeCustomAttr(Builder $query, $academic_calendar): void
    {
        $query
            ->select('tbl_schedule_history.*', 'session_label')
            ->join('tbl_session_detail', 'tbl_session_detail.id_session_detail', '=', 'tbl_schedule_history.id_session_detail')
            ->join('tbl_academic_calendar', 'tbl_academic_calendar.id_academic_calendar', '=', 'tbl_schedule_history.id_academic_calendar')
            ->join('tbl_academic_year', 'tbl_academic_year.id_academic_year', '=', 'tbl_academic_calendar.id_academic_year')
            ->join('tbl_semester', 'tbl_semester.id_semester', '=', 'tbl_academic_calendar.id_semester')
            ->join('tbl_shift', 'tbl_shift.id_shift', '=', 'tbl_academic_calendar.id_shift')
            ->join('tbl_stage', 'tbl_stage.id_stage', '=', 'tbl_academic_calendar.id_stage')

            ->selectRaw(
                '(CASE
				WHEN tbl_academic_calendar.id_academic_calendar = "' . $academic_calendar->id_academic_calendar . '"
				THEN "self"
				WHEN tbl_academic_calendar.start_date_en < "' . $academic_calendar->getRawOriginal('start_date_en') . '"
				THEN "prev"
				WHEN tbl_academic_calendar.start_date_en > "' . $academic_calendar->getRawOriginal('start_date_en') . '"
				THEN "next"
				WHEN tbl_academic_calendar.id_academic_calendar < "' . $academic_calendar->id_academic_calendar . '"
				THEN "prev"
				WHEN tbl_academic_calendar.id_academic_calendar > "' . $academic_calendar->id_academic_calendar . '"
				THEN "next"
				END) AS type'
            )

            ->selectRaw('CONCAT(
				"xxx-academic-year=", tbl_academic_year.id_academic_year,"&",
				"xxx-semester=", tbl_semester.id_semester,"&",
				"xxx-shift=", tbl_shift.id_shift,"&",
				"xxx-academic-calendar=", tbl_academic_calendar.id_academic_calendar
			) AS slug')

            ->with([
                'session_detail',
                'subject',
                'room',
                'employees',
                'groups',
            ]);
    }
    public function scopeHistoryRule(Builder $query, $date, $id_day, $id_session): void
    {
        $query
            ->when($date !== '*', function ($q) use ($date) {
                $q->where('tbl_schedule_history.updated_at', '>=', $date)->where('tbl_schedule_history.updated_at', '<', date('Y-m-d', strtotime('+1 days', strtotime($date))));
            })
            ->when($id_day !== '*', function ($q) use ($id_day) {
                $q->whereHas('session_detail', function ($session_detail) use ($id_day) {
                    $session_detail->where('id_day', $id_day);
                });
            })
            ->when($id_session !== '*', function ($q) use ($id_session) {
                $q->whereHas('session_detail', function ($session_detail) use ($id_session) {
                    $session_detail->where('id_session', $id_session);
                });
            })
            ->with([
                'updater:id_user,user_label',
                'deleter:id_user,user_label',
            ]);
    }


    public function scopeByRoom(Builder $query, $id_room, $academic_calendar, $date, $id_day, $id_session): void
    {
        $query->where(function ($q) use ($id_room) {
            $q->where('id_room', $id_room)
                ->orWhereHas('parent', function ($q) use ($id_room) {
                    $q->where('id_room', $id_room);
                });
        })
            ->AcademicCalendarRule($academic_calendar)
            ->CustomAttr($academic_calendar)
            ->HistoryRule($date, $id_day, $id_session)
            ->selectRaw(
                '(CASE
				WHEN tbl_schedule_history.id_room <> ' . $id_room . '
				THEN tbl_schedule_history.updated_at
				ELSE tbl_schedule_history.deleted_at
				END) as deleted_at'
            )
            ->selectRaw(
                '(CASE
				WHEN tbl_schedule_history.id_room <> ' . $id_room . '
				THEN tbl_schedule_history.updated_by
				ELSE tbl_schedule_history.deleted_by
				END) as deleted_by'
            );
    }
    public function scopeByGroup(Builder $query, $id_group, $academic_calendar, $date, $id_day, $id_session): void
    {
        $query->where(function ($q) use ($id_group) {
            $q->whereHas('groups', function ($q) use ($id_group) {
                $q->where('tbl_group.id_group', $id_group);
            })->orWhereHas('parent.groups', function ($q) use ($id_group) {
                $q->where('tbl_group.id_group', $id_group);
            });
        })
            ->AcademicCalendarRule($academic_calendar)
            ->CustomAttr($academic_calendar)
            ->HistoryRule($date, $id_day, $id_session)
            ->leftJoin('tbl_join_group_history', function ($join) use ($id_group) {
                $join->on('tbl_join_group_history.id_schedule_history', '=', 'tbl_schedule_history.id_schedule_history')
                    ->where('tbl_join_group_history.id_group', '=', $id_group);
            })
            ->selectRaw(
                '(CASE
				WHEN tbl_join_group_history.id_group IS NULL
				THEN tbl_schedule_history.updated_at
				ELSE tbl_schedule_history.deleted_at
				END) as deleted_at'
            )
            ->selectRaw(
                '(CASE
				WHEN tbl_join_group_history.id_group IS NULL
				THEN tbl_schedule_history.updated_by
				ELSE tbl_schedule_history.deleted_by
				END) as deleted_by'
            )
            ->with('groups', function ($q) use ($id_group) {
                $q->where('tbl_group.id_group', "<>", $id_group);
            });
    }
    public function scopeByEmployee(Builder $query, $id_employee, $academic_calendar, $date, $id_day, $id_session): void
    {
        $query->where(function ($q) use ($id_employee) {
            $q->whereHas('employees', function ($q) use ($id_employee) {
                $q->where('tbl_employee.id_employee', $id_employee);
            })->orWhereHas('parent.employees', function ($q) use ($id_employee) {
                $q->where('tbl_employee.id_employee', $id_employee);
            });
        })
            ->AcademicCalendarRule($academic_calendar)
            ->CustomAttr($academic_calendar)
            ->HistoryRule($date, $id_day, $id_session)
            ->leftJoin('tbl_join_teacher_history', function ($join) use ($id_employee) {
                $join->on('tbl_join_teacher_history.id_schedule_history', '=', 'tbl_schedule_history.id_schedule_history')
                    ->where('tbl_join_teacher_history.id_employee', '=', $id_employee);
            })
            ->selectRaw(
                '(CASE
				WHEN tbl_join_teacher_history.id_employee IS NULL
				THEN tbl_schedule_history.updated_at
				ELSE tbl_schedule_history.deleted_at
				END) as deleted_at'
            )
            ->selectRaw(
                '(CASE
				WHEN tbl_join_teacher_history.id_employee IS NULL
				THEN tbl_schedule_history.updated_by
				ELSE tbl_schedule_history.deleted_by
				END) as deleted_by'
            )
            ->with('employees', function ($q) use ($id_employee) {
                $q->where('tbl_employee.id_employee', "<>", $id_employee);
            });
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function academic_calendar()
    {
        return $this->belongsTo(AcademicCalendar::class, 'id_academic_calendar');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    public function session_detail()
    {
        return $this->belongsTo(SessionDetail::class, 'id_session_detail');
    }

    public function parent()
    {
        return $this->belongsTo(ScheduleHistory::class, 'id_parent');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function join_group_histories()
    {
        return $this->hasMany(JoinGroupHistory::class, 'id_schedule_history');
    }

    public function join_teacher_histories()
    {
        return $this->hasMany(JoinTeacherHistory::class, 'id_schedule_history');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'tbl_join_teacher_history', 'id_schedule_history', 'id_employee');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'tbl_join_group_history', 'id_schedule_history', 'id_group');
    }

    public function session(): HasOneThrough
    {
        return $this->hasOneThrough(
            Session::class,
            SessionDetail::class,
            'id_session_detail', # foreign key on intermediary -- SessionDetail
            'id_session', # foreign key on target -- Session
            'id_session_detail', # local key on this -- ScheduleHistory
            'id_session' # local key on intermediary -- SessionDetail
        );
    }

    public function day(): HasOneThrough
    {
        return $this->hasOneThrough(
            Day::class,
            SessionDetail::class,
            'id_session_detail',
            'id_day',
            'id_session_detail',
            'id_day'
        );
    }
}
