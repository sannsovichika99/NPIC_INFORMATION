<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserPermission extends Model
{
	protected $table = 'tbl_user_permission';
	protected $primaryKey = 'id_user_permission';
	public $timestamps = false;

	protected $casts = [
		'id_user' => 'int',
		'id_permission' => 'int'
	];

	protected $fillable = [
		'id_user',
		'id_permission'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_user');
	}

	public function permission()
	{
		return $this->belongsTo(Permission::class, 'id_permission');
	}

	public function user_permission_details()
	{
		return $this->hasMany(UserPermissionDetail::class, 'id_user_permission');
	}
}
