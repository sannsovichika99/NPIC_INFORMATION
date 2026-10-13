<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Village extends Model
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
			$model->unit_kh = 'ភូមិ';
			$model->unit_en = 'Village';
			$model->unit_latin = 'Phum';
		});
		static::addGlobalScope('boot', function (Builder $builder) {
			$builder->where('unit_en', 'Village')
				->orderBy('name_kh', 'asc');
		});
	}

	public function commune()
	{
		return $this->belongsTo(Commune::class, 'id_parent');
	}
}
