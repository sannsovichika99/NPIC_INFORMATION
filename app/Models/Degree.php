<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Degree extends Model
{
	protected $table = 'tbl_degree';
	protected $primaryKey = 'id_degree';
	public $timestamps = false;

	protected $fillable = [
		'degree_en',
		'degree_kh',
		'short_name',
		'degree_rank',
		'degree_group',
	];

	public function employees()
	{
		return $this->hasMany(Employee::class, 'id_highest_degree');
	}

	public function groups()
	{
		return $this->hasMany(Group::class, 'id_degree');
	}
}
