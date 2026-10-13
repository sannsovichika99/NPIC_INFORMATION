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
class DivisionSubject extends Model
{
	use TimeStamps, TracksCreator, TracksUpdater;
	protected $table = 'tbl_division_subject';
	protected $primaryKey = 'id_division_subject';

	protected $casts = [
		'id_division' => 'int',
		'id_subject' => 'int',
		'id_subject_type' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'id_division',
		'id_subject',
		'id_subject_type',
		'record_status',
		'created_by',
		'updated_by'
	];

	public function scopeEnabled(Builder $query): void
	{
		$query->where('record_status', 'ENABLED')
			->whereHas('subject', function ($q) {
				$q->Enabled();
			});
	}
	public function scopeDisabled(Builder $query): void
	{
		$query->where('record_status', 'DISABLED')
			->orWhereHas('subject', function ($q) {
				$q->Disabled();
			});
	}

	public function division()
	{
		return $this->belongsTo(Division::class, 'id_division');
	}

	public function subject()
	{
		return $this->belongsTo(Subject::class, 'id_subject');
	}

	public function subject_type()
	{
		return $this->belongsTo(SubjectType::class, 'id_subject_type');
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
