<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Stage extends Model
{
	protected $table = 'tbl_stage';
	protected $primaryKey = 'id_stage';
	public $timestamps = false;

	protected $fillable = [
		'stage_en',
		'stage_kh'
	];
}
