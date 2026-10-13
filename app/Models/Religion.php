<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
	protected $table = 'tbl_religion';
	protected $primaryKey = 'id_religion';
	public $timestamps = false;

	protected $fillable = [
		'rel_en',
		'rel_kh',
		'rel_label'
	];
}
