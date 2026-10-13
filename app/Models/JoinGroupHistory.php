<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class JoinGroupHistory extends Model
{
	protected $table = 'tbl_join_group_history';
	protected $primaryKey = 'id_join_group_history';
	public $timestamps = false;

	protected $casts = [
		'id_schedule_history' => 'int',
		'id_group' => 'int'
	];
	
	protected $fillable = [
		'id_schedule_history',
		'id_group'
	];

	public function schedule_history()
	{
		return $this->belongsTo(ScheduleHistory::class, 'id_schedule_history');
	}

	public function group()
	{
		return $this->belongsTo(Group::class, 'id_group');
	}
}
