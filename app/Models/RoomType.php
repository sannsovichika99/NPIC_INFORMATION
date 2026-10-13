<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class RoomType extends Model
{
	protected $table = 'tbl_room_type';
	protected $primaryKey = 'id_room_type';
	public $timestamps = false;

	protected $fillable = [
		'type_en',
		'type_kh'
	];

	public function rooms()
	{
		return $this->hasMany(Room::class, 'id_room_type');
	}
}
