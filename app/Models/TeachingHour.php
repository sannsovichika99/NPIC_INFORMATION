<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class TeachingHour extends Model
{
	use TimeStamps, TracksCreator, TracksUpdater;
	protected $table = 'tbl_teaching_hour';
	protected $primaryKey = 'id_teaching_hour';

	protected $casts = [
		'min_hour' => 'int',
		'max_hour' => 'int',
		'id_shift' => 'int',
		'id_employee' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'min_hour',
		'max_hour',
		'from_date',
		'id_shift',
		'id_employee',
		'created_by',
		'updated_by'
	];

	protected function FromDate(): Attribute
	{
		return Attribute::make(
			get: fn (string $value) => Carbon::parse($value)->format('d-m-Y'),
			set: fn (string $value) => Carbon::parse($value)->format('Y-m-d'),
		);
	}

	public function shift()
	{
		return $this->belongsTo(Shift::class, 'id_shift');
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'id_employee');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updater()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}
}
