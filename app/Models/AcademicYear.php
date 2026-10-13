<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AcademicYear extends Model
{
	protected $table = 'tbl_academic_year';
	protected $primaryKey = 'id_academic_year';
	public $timestamps = false;

	protected $fillable = [
		'year_en',
		'year_kh'
	];
}
