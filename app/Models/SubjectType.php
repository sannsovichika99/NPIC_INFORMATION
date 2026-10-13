<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SubjectType extends Model
{
	protected $table = 'tbl_subject_type';
	protected $primaryKey = 'id_subject_type';
	public $timestamps = false;

	protected $fillable = [
		'type_en',
		'type_kh'
	];
}
