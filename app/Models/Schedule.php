<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Schedule extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected static $tracksUpdaterChildren = [
        JoinGroup::class => 'id_schedule',
        JoinTeacher::class => [                                     // with exclusions
            'foreignKey' => 'id_schedule',
            'excludeKeys' => ['fee_status'],
        ],
    ];
    // protected static $tracksUpdaterExcludeKeys = ['approved_at'];

    protected $table = 'tbl_schedule';
    protected $primaryKey = 'id_schedule';

    protected $casts = [
        'id_room' => 'int',
        'id_academic_calendar' => 'int',
        'id_subject' => 'int',
        'id_session_detail' => 'int',
        'id_semester' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
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
        'id_semester',
        'created_by',
        'updated_by',
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
        $query
            ->where('from_date', '<=', $academic_calendar->getRawOriginal('end_date_en'))
            ->where('to_date', '>=', $academic_calendar->getRawOriginal('start_date_en'))
            ->whereHas('academic_calendar', function ($q) use ($academic_calendar) {
                $q
                    ->where('start_date_en', '<=', $academic_calendar->getRawOriginal('end_date_en'))
                    ->where('end_date_en', '>=', $academic_calendar->getRawOriginal('start_date_en'));
                // ->where('id_shift', $academic_calendar->id_shift);
            })->whereHas('session_detail', function ($q) use ($academic_calendar) {
                $q->whereIn('session_label', $academic_calendar->session_details()->pluck('session_label'));
            });
    }

    public function scopeSessionRule(Builder $query, $session_label, $from_date, $to_date, $strict): void
    {
        if ($from_date !== null && $to_date !== null) {
            $from_date = Carbon::parse($from_date)->format('Y-m-d');
            $to_date = Carbon::parse($to_date)->format('Y-m-d');
            if ($strict) {
                $query->where('from_date', '=', $from_date)
                    ->where('to_date', '=', $to_date);
            } else {
                $query->where('from_date', '<=', $to_date)
                    ->where('to_date', '>=', $from_date);
            }
        }
        if ($session_label !== null) {
            $query->whereHas('session_detail', function ($q) use ($session_label) {
                $q->where('session_label', $session_label);
            });
        }
    }

    public function scopeCustomAttr(Builder $query, $academic_calendar): void
    {
        $query
            ->select('tbl_schedule.*', 'session_label')
            ->join('tbl_session_detail', 'tbl_session_detail.id_session_detail', '=', 'tbl_schedule.id_session_detail')
            ->join('tbl_academic_calendar', 'tbl_academic_calendar.id_academic_calendar', '=', 'tbl_schedule.id_academic_calendar')
            ->join('tbl_academic_year', 'tbl_academic_year.id_academic_year', '=', 'tbl_academic_calendar.id_academic_year')
            ->join('tbl_semester', 'tbl_semester.id_semester', '=', 'tbl_academic_calendar.id_semester')
            ->join('tbl_shift', 'tbl_shift.id_shift', '=', 'tbl_academic_calendar.id_shift')
            ->join('tbl_stage', 'tbl_stage.id_stage', '=', 'tbl_academic_calendar.id_stage')

            ->selectRaw(
                '(CASE
				WHEN (
					EXISTS(SELECT * FROM tbl_join_group WHERE tbl_join_group.id_schedule = tbl_schedule.id_schedule)
					AND EXISTS(SELECT * FROM tbl_join_teacher WHERE tbl_join_teacher.id_schedule = tbl_schedule.id_schedule)
				)
				THEN 1
				ELSE 0
				END) AS fulfilled'
            )

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

            ->orderBy('id_session', 'asc')
            ->orderBy('id_day', 'asc')
            ->orderBy('from_date', 'asc')
            ->orderBy('to_date', 'asc')

            ->with([
                'session_detail',
                'subject',
                'room',
                'employees',
                'groups',
            ]);
    }

    public function scopeByRoom(Builder $query, $id_room, $academic_calendar, $session_label = null, $from_date = null, $to_date = null, $strict = false): void
    {
        if ($from_date !== null && $to_date !== null) {
            $this->isDateRangeInCalendarDateRange($academic_calendar, $from_date, $to_date);
        }
        $query->where('id_room', $id_room)
            ->AcademicCalendarRule($academic_calendar)
            ->SessionRule($session_label, $from_date, $to_date, $strict)
            ->CustomAttr($academic_calendar);
    }
    public function scopeByGroup(Builder $query, $id_group, $academic_calendar, $session_label = null, $from_date = null, $to_date = null, $strict = false): void
    {
        if ($from_date !== null && $to_date !== null) {
            $this->isDateRangeInCalendarDateRange($academic_calendar, $from_date, $to_date);
        }
        $query->whereHas('groups', function ($q) use ($id_group) {
            $q->where('tbl_group.id_group', $id_group);
        })
            ->AcademicCalendarRule($academic_calendar)
            ->SessionRule($session_label, $from_date, $to_date, $strict)
            ->CustomAttr($academic_calendar)
            ->with('groups', function ($q) use ($id_group) {
                $q->where('tbl_group.id_group', "<>", $id_group);
            });
    }
    public function scopeByEmployee(Builder $query, $id_employee, $academic_calendar, $session_label = null, $from_date = null, $to_date = null, $strict = false): void
    {
        if ($from_date !== null && $to_date !== null) {
            $this->isDateRangeInCalendarDateRange($academic_calendar, $from_date, $to_date);
        }
        $query
            ->whereHas('employees', function ($q) use ($id_employee) {
                $q->where('tbl_employee.id_employee', $id_employee); // useless condition
            })
            ->AcademicCalendarRule($academic_calendar)
            ->SessionRule($session_label, $from_date, $to_date, $strict)
            ->CustomAttr($academic_calendar)
            ->join('tbl_join_teacher', function ($join) use ($id_employee) {
                $join->on('tbl_join_teacher.id_schedule', '=', 'tbl_schedule.id_schedule')
                    ->where('tbl_join_teacher.id_employee', $id_employee);
            })
            ->selectRaw('tbl_join_teacher.fee_status')
            ->with('employees', function ($q) use ($id_employee) {
                $q->where('tbl_employee.id_employee', "<>", $id_employee);
            });
    }
    public function scopeBySubject(Builder $query, $id_subject, $academic_calendar, $session_label = null, $from_date = null, $to_date = null, $strict = false): void
    {
        if ($from_date !== null && $to_date !== null) {
            $this->isDateRangeInCalendarDateRange($academic_calendar, $from_date, $to_date);
        }
        $query->where('id_subject', $id_subject)
            ->AcademicCalendarRule($academic_calendar)
            ->SessionRule($session_label, $from_date, $to_date, $strict)
            ->CustomAttr($academic_calendar);
    }

    public function scopeByRoomNoOverLay(Builder $query, $id_room, $academic_calendar, $session_label = null, $from_date = null, $to_date = null): void
    {
        $query->ByRoom($id_room, $academic_calendar, $session_label, $from_date, $to_date)
            ->where('tbl_schedule.id_academic_calendar', $academic_calendar->id_academic_calendar);
    }
    public function scopeByGroupNoOverLay(Builder $query, $id_group, $academic_calendar, $session_label = null, $from_date = null, $to_date = null): void
    {
        $query->ByGroup($id_group, $academic_calendar, $session_label, $from_date, $to_date)
            ->where('tbl_schedule.id_academic_calendar', $academic_calendar->id_academic_calendar);
    }
    public function scopeByEmployeeNoOverLay(Builder $query, $id_employee, $academic_calendar, $session_label = null, $from_date = null, $to_date = null): void
    {
        $query->ByEmployee($id_employee, $academic_calendar, $session_label, $from_date, $to_date)
            ->where('tbl_schedule.id_academic_calendar', $academic_calendar->id_academic_calendar);
    }

    private function isDateRangeInCalendarDateRange($academic_calendar, $from_date, $to_date)
    {
        $start_date = $academic_calendar->start_date_en;
        $end_date = $academic_calendar->end_date_en;
        if (!$start_date || !$end_date || !$from_date || !$to_date) {
            return ResponseHelper::customErrorMsg('isDateRangeInCalendarDateRange function corrupted! (Condition 1st)', 422);
        }

        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);
        $from = Carbon::parse($from_date);
        $to = Carbon::parse($to_date);

        if ($start->greaterThan($end)) {
            return ResponseHelper::customErrorMsg('isDateRangeInCalendarDateRange function corrupted! (Condition 2nd)', 422);
        }
        if ($from->greaterThan($to)) {
            return ResponseHelper::customErrorMsg('isDateRangeInCalendarDateRange function corrupted! (Condition 3rd)', 422);
        }
        if ($from->lessThan($start)) {
            return ResponseHelper::customErrorMsg('isDateRangeInCalendarDateRange function corrupted! (Condition 4th)', 422);
        }
        if ($to->greaterThan($end)) {
            return ResponseHelper::customErrorMsg('isDateRangeInCalendarDateRange function corrupted! (Condition 5th)', 422);
        }
        return true;
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function join_groups()
    {
        return $this->hasMany(JoinGroup::class, 'id_schedule');
    }

    public function join_teachers()
    {
        return $this->hasMany(JoinTeacher::class, 'id_schedule');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'tbl_join_teacher', 'id_schedule', 'id_employee');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'tbl_join_group', 'id_schedule', 'id_group');
    }

    public function delete()
    {
        DB::beginTransaction();

        $this->join_groups->each->delete();
        $this->join_teachers->each->delete();

        $result = parent::delete();

        DB::commit();

        return $result;
    }
}
