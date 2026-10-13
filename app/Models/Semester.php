<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Semester extends Model
{
	protected $table = 'tbl_semester';
	protected $primaryKey = 'id_semester';
	public $timestamps = false;

	protected $fillable = [
		'semester_en',
		'semester_kh'
	];
}
