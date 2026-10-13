<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Sibling extends Model
{
	protected $table = 'tbl_sibling';
	protected $primaryKey = 'id_sibling';
	public $timestamps = false;

	protected $casts = [
		'id_gender' => 'int',
		'id_student' => 'int'
	];

	protected $fillable = [
		'name_en',
		'name_kh',
		'dob',
		'job',
		'id_gender',
		'id_student'
	];
	protected function NameEn(): Attribute
	{
		return Attribute::make(
			get: fn(string $value) => strtoupper($value),
			set: fn(string $value) => strtoupper($value),
		);
	}
	
	public function gender()
	{
		return $this->belongsTo(Gender::class, 'id_gender');
	}

	public function student()
	{
		return $this->belongsTo(Student::class, 'id_student');
	}
}
