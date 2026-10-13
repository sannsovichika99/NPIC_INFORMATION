<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class JoinGroup extends Model
{
	protected $table = 'tbl_join_group';
	protected $primaryKey = 'id_join_group';
	public $timestamps = false;

	protected $casts = [
		'id_schedule' => 'int',
		'id_group' => 'int'
	];

	protected $fillable = [
		'id_schedule',
		'id_group'
	];

	public function schedule()
	{
		return $this->belongsTo(Schedule::class, 'id_schedule');
	}

	public function group()
	{
		return $this->belongsTo(Group::class, 'id_group');
	}
}
