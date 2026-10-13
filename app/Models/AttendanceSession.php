<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $table = 'tbl_attendance_session';

    protected $primaryKey = 'id_attendance_session';

    public $timestamps = false;

    protected $fillable = ['id_attendance', 'id_session'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'id_attendance');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'id_session');
    }
}
