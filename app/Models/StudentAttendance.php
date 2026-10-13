<?php

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_student_attendance';

    protected $primaryKey = 'id_student_attendance';

    protected $fillable = [
        'remark',
        'attendance_status',
        'id_attendance',
        'id_student_enrollment',
        'created_by',
        'updated_by',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'id_attendance');
    }

    public function student_enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'id_student_enrollment', 'id_student_enrollment');
    }

    public function student()
    {
        return $this->hasOneThrough(Student::class, StudentEnrollment::class, 'id_student_enrollment', 'id_student', 'id_student_enrollment', 'id_student');
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
