<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class JoinTeacherHistory extends Model
{
	protected $table = 'tbl_join_teacher_history';
	protected $primaryKey = 'id_join_teacher_history';
	public $timestamps = false;

	protected $casts = [
		'id_schedule_history' => 'int',
		'id_employee' => 'int'
	];

	protected $fillable = [
		'id_schedule_history',
		'id_employee'
	];

	public function schedule_history()
	{
		return $this->belongsTo(ScheduleHistory::class, 'id_schedule_history');
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'id_employee');
	}
}
