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

class EmployeeAttr extends Model
{
	use TimeStamps, TracksCreator, TracksUpdater;
	protected $table = 'tbl_employee_attr';
	protected $primaryKey = 'id_employee_attr';

	protected $casts = [
		'id_employee' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'id_employee',
		'from_date',
		'emp_status',
		'emp_type',
		'emp_role',
		'created_by',
		'updated_by'
	];

	protected function FromDate(): Attribute
	{
		return Attribute::make(
			get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
			set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
		);
	}

	public function id_employee()
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
