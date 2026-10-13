<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Traits\ParentCompositePrimaryKey;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Father extends Model
{
	use ParentCompositePrimaryKey;
	protected $table = 'tbl_parent';
	protected $primaryKey = 'id_student';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_student' => 'int',
		'id_nationality' => 'int',
		'id_ethnicity' => 'int',
		'id_por' => 'int',
	];

	protected $fillable = [
		'id_student',
		'role',
		'name_en',
		'name_kh',
		'dob',
		'job',
		'home_no',
		'street_no',
		'id_por',
		'vital_status',
		'id_nationality',
		'id_ethnicity'
	];

	protected $appends = [
		'id_father_por_province',
		'id_father_por_district',
		'id_father_por_commune',
		'id_father_por_village',
	];

	protected $attributes = [
		'role' => 'FATHER'
	];

	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope('boot', function (Builder $builder) {
			$builder->where('role', 'FATHER');
		});
	}
	protected function NameEn(): Attribute
	{
		return Attribute::make(
			get: fn(string $value) => strtoupper($value),
			set: fn(string $value) => strtoupper($value),
		);
	}

	protected function porVillage()
	{
		return Village::find($this->id_por);
	}
	protected function porCommune()
	{
		return Commune::find($this->porVillage()?->id_parent ?? $this->id_por);
	}
	protected function porDistrict()
	{
		return District::find($this->porCommune()?->id_parent ?? $this->id_por);
	}
	protected function porProvince()
	{
		return Province::find($this->porDistrict()?->id_parent ?? $this->id_por);
	}

	protected function getIdFatherPorProvinceAttribute()
	{
		return $this->porProvince()?->id_geography;
	}
	protected function getIdFatherPorDistrictAttribute()
	{
		return $this->porDistrict()?->id_geography;
	}
	protected function getIdFatherPorCommuneAttribute()
	{
		return $this->porCommune()?->id_geography;
	}
	protected function getIdFatherPorVillageAttribute()
	{
		return $this->porVillage()?->id_geography;
	}

	public function nationality()
	{
		return $this->belongsTo(Nationality::class, 'id_nationality');
	}

	public function ethnicity()
	{
		return $this->belongsTo(Ethnicity::class, 'id_ethnicity');
	}

	public function student()
	{
		return $this->belongsTo(Student::class, 'id_student');
	}
}
