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
class RoomDivision extends Model
{
	use TimeStamps, TracksCreator, TracksUpdater;
	protected $table = 'tbl_room_division';
	protected $primaryKey = 'id_room_division';

	protected $casts = [
		'id_division' => 'int',
		'id_room' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'id_division',
		'id_room',
		'from_date',
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

	public function division()
	{
		return $this->belongsTo(Division::class, 'id_division');
	}

	public function room()
	{
		return $this->belongsTo(Room::class, 'id_room');
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
