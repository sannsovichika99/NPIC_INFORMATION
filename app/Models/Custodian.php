<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Custodian extends Model
{
	protected $table = 'tbl_custodian';
	protected $primaryKey = 'id_student';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_student' => 'int'
	];

	protected $fillable = [
		'id_student',
		'name_en',
		'name_kh',
		'job',
		'home_no',
		'street_no',
		'phone1',
		'phone2'
	];
	protected function NameEn(): Attribute
	{
		return Attribute::make(
			get: fn(string $value) => strtoupper($value),
			set: fn(string $value) => strtoupper($value),
		);
	}
	public function student()
	{
		return $this->belongsTo(Student::class, 'id_student');
	}
}
