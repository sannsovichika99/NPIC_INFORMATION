<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Grade extends Model
{
	protected $table = 'tbl_grade';
	protected $primaryKey = 'id_grade';
	public $timestamps = false;

	protected $fillable = [
		'grade_en',
		'grade_kh'
	];

	public function groups()
	{
		return $this->hasMany(Group::class, 'id_grade');
	}
}
