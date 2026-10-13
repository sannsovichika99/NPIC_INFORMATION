<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Building extends Model
{
	use TimeStamps, TracksCreator, TracksUpdater;
	protected $table = 'tbl_building';
	protected $primaryKey = 'id_building';

	protected $casts = [
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'name_en',
		'name_kh',
		'record_status',
		'created_by',
		'updated_by'
	];

	public function scopeEnabled(Builder $query): void
	{
		$query->where('record_status', 'ENABLED');
	}
	public function scopeDisabled(Builder $query): void
	{
		$query->where('record_status', 'DISABLED');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updater()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function rooms()
	{
		return $this->hasMany(Room::class, 'id_building');
	}
}
