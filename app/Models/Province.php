<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\District;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Province extends Model
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
		static::creating(function ($model) {
			$model->unit_kh = 'ខេត្ត';
			$model->unit_en = 'Province';
			$model->unit_latin = 'Khaet';
		});
		static::addGlobalScope('boot', function (Builder $builder) {
			$builder->whereIn('unit_en', ['Province', 'Capital'])
				->orderBy('name_kh', 'asc');
		});
	}

	public function districts()
	{
		return $this->hasMany(District::class, 'id_parent');
	}
}
