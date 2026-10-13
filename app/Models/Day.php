<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Day extends Model
{
	protected $table = 'tbl_day';
	protected $primaryKey = 'id_day';
	public $timestamps = false;

	protected $fillable = [
		'day_en',
		'day_kh',
		'short_name',
		'day_rank',
	];

	public function scopeByShift(Builder $query, $id_shift): void
	{
		$query->whereHas('session_details', function ($q) use ($id_shift) {
			$q->where('id_shift', $id_shift);
		})->orderBy('day_rank','asc');
	}


	public function session_details()
	{
		return $this->hasMany(SessionDetail::class, 'id_day');
	}
}
