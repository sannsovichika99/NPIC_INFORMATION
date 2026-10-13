<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected static $tracksUpdaterChildren = [
        AttendanceSession::class => 'id_attendance',
    ];

    protected $table = 'tbl_attendance';

    protected $primaryKey = 'id_attendance';

    protected $fillable = [
        'id_academic_group',
        'id_subject',
        'id_room',
        'record_date',
        'lesson',
        'task',
        'created_by',
        'updated_by',
    ];

    protected function RecordDate(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    public function academic_group()
    {
        return $this->belongsTo(AcademicGroup::class, 'id_academic_group');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    public function attendance_sessions()
    {
        return $this->hasMany(AttendanceSession::class, 'id_attendance');
    }

    public function sessions()
    {
        return $this->belongsToMany(
            Session::class,
            'tbl_attendance_session',
            'id_attendance',
            'id_session'
        );
    }

    public function student_attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'id_attendance');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
