<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Title extends Model
{
	protected $table = 'tbl_title';
	protected $primaryKey = 'id_title';
	public $timestamps = false;

	protected $casts = [
		'id_gender' => 'int'
	];

	protected $fillable = [
		'title_en',
		'title_kh',
		'id_gender'
	];

	public function id_gender()
	{
		return $this->belongsTo(Gender::class, 'id_gender');
	}
}
