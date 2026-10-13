<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserPermissionDetail extends Model
{
	protected $table = 'tbl_user_permission_detail';
	protected $primaryKey = 'id_user_permission_detail';
	public $timestamps = false;

	protected $casts = [
		'id_user_permission' => 'int',
		'id_division' => 'int',
		'id_shift' => 'int'
	];

	protected $fillable = [
		'id_user_permission',
		'id_division',
		'id_shift'
	];

	public function user_permission()
	{
		return $this->belongsTo(UserPermission::class, 'id_user_permission');
	}

	public function division()
	{
		return $this->belongsTo(Division::class, 'id_division');
	}

	public function shift()
	{
		return $this->belongsTo(Shift::class, 'id_shift');
	}
}
