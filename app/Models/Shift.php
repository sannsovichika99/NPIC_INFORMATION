<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Shift extends Model
{
	protected $table = 'tbl_shift';
	protected $primaryKey = 'id_shift';
	public $timestamps = false;

	protected $fillable = [
		'shift_en',
		'shift_kh',
		'shift_label',
	];

	public function user_permission_details()
	{
		return $this->hasMany(UserPermissionDetail::class, 'id_shift');
	}
}
