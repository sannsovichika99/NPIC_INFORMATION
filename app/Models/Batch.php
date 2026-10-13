<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
	protected $table = 'tbl_batch';
	protected $primaryKey = 'id_batch';
	public $timestamps = false;

	protected $fillable = [
		'batch_en',
		'batch_kh'
	];
}
