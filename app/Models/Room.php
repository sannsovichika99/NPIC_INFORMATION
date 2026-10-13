<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Room extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_room';
    protected $primaryKey = 'id_room';

    protected $casts = [
        'room_qty' => 'int',
        'id_building' => 'int',
        'id_division' => 'int',
        'id_room_type' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'room_name',
        'room_qty',
        'record_status',
        'avai_status',
        'id_building',
        'id_division',
        'id_room_type',
        'created_by',
        'updated_by'
    ];

    protected $with = ['division'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder
                ->orderByRaw('CAST(REGEXP_REPLACE(SUBSTRING_INDEX(room_name, " ", 1), "[^0-9]", "")AS UNSIGNED) asc')
                ->orderBy('tbl_room.id_building', 'asc')
                ->orderBy('tbl_room.room_name', 'asc');
        });
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => "xxx-main-division={$this->division?->id_division}&xxx-room={$this->id_room}"
        );
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED')
            ->whereHas('division', function ($q) {
                $q->Enabled();
            });
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED')
            ->orWhereHas('division', function ($q) {
                $q->Disabled();
            });
    }
    public function scopeAllowed(Builder $query): void
    {
        $query->where('avai_status', 'ALLOWED');
    }
    public function scopeDenied(Builder $query): void
    {
        $query->where('avai_status', 'DENIED');
    }
    public function scopeSchedulable(Builder $query, $id_room): void
    {
        $query
            ->where('id_room', $id_room)
            ->where('id_room_type', '<>', '1')
            ->Enabled();
    }
    public function scopeCrossSchedulable(Builder $query, $id_room): void
    {
        $query->Schedulable($id_room)
            ->Allowed();
    }
    public function scopeSchedulables(Builder $query, $id_division): void
    {
        $query
            ->where('id_division', $id_division)
            ->where('id_room_type', '<>', '1')
            ->Enabled();
    }
    public function scopeCrossSchedulables(Builder $query, $id_division): void
    {
        $query->Schedulables($id_division)
            ->Allowed();
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'id_building');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_division');
    }

    public function room_type()
    {
        return $this->belongsTo(RoomType::class, 'id_room_type');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_room');
    }

    public function schedule_histories()
    {
        return $this->hasMany(ScheduleHistory::class, 'id_room');
    }

    public function room_divisions()
    {
        return $this->hasMany(RoomDivision::class, 'id_room');
    }
}
