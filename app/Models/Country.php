<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
	protected $table = 'tbl_country';
	protected $primaryKey = 'id_country';

	public $timestamps = false;

	protected $casts = [
		'id_country' => 'int',
	];

	protected $fillable = [
		'id_country',
		'name_en',
		'name_kh',
		'code',
	];
}
