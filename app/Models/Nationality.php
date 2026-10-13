<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Nationality extends Model
{
	protected $table = 'tbl_nationality';
	protected $primaryKey = 'id_nationality';
	public $timestamps = false;

	protected $fillable = [
		'nat_en',
		'nat_kh',
		'nat_label'
	];
}
