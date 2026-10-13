<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Ethnicity extends Model
{
	protected $table = 'tbl_ethnicity';
	protected $primaryKey = 'id_ethnicity';
	public $timestamps = false;

	protected $fillable = [
		'eth_en',
		'eth_kh',
		'eth_label'
	];
}
