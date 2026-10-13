<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JoinTeacher extends Model
{
	protected $table = 'tbl_join_teacher';
	protected $primaryKey = 'id_join_teacher';
	public $timestamps = false;

	protected $casts = [
		'id_schedule' => 'int',
		'id_employee' => 'int',
	];
	
	protected $fillable = [
		'id_schedule',
		'id_employee',
		'fee_status',
	];

	public function schedule()
	{
		return $this->belongsTo(Schedule::class, 'id_schedule');
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'id_employee');
	}
}
