<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Commune extends Model
{
	protected $table = 'tbl_geography';
	protected $primaryKey = 'id_geography';

	public $timestamps = false;

	protected $casts = [
		'id_geography' => 'int',
		'id_parent' => 'int'
	];

	protected $fillable = [
		'id_geography',
		'name_en',
		'name_kh',
		'name_latin',
		'unit_en',
		'unit_kh',
		'unit_latin',
		'id_parent'
	];

	protected static function boot()
	{
		parent::boot();
		static::saving(function ($model) {
			$district = District::find($model->id_parent);
			$province = Province::find($district->id_parent);
			if ($province->unit_en === 'Capital') {
				$model->unit_kh = 'សង្កាត់';
				$model->unit_en = 'Quarter';
				$model->unit_latin = 'Sangkat';
			} else {
				$model->unit_kh = 'ឃុំ';
				$model->unit_en = 'Commune';
				$model->unit_latin = 'Khum';
			}
		});
		static::addGlobalScope('boot', function (Builder $builder) {
			$builder->whereIn('unit_en', ['Commune', 'Quarter'])
				->orderBy('name_kh', 'asc');
		});
	}

	public function district()
	{
		return $this->belongsTo(District::class, 'id_parent');
	}

	public function villages()
	{
		return $this->hasMany(Village::class, 'id_parent');
	}
}
