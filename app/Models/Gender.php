<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Gender extends Model
{
	protected $table = 'tbl_gender';
	protected $primaryKey = 'id_gender';
	public $timestamps = false;

	protected $fillable = [
		'gender_en',
		'gender_en_full',
		'gender_kh',
		'gender_kh_full'
	];
}
