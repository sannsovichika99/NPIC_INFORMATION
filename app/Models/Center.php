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
class Center extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_division';
    protected $primaryKey = 'id_division';

    protected $casts = [
        'id_parent' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'name_en',
        'name_kh',
        'short_name',
        'id_parent',
        'record_status',
        'label',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->where('label', 'Center')
                ->whereNull('id_parent')
                ->orderBy('name_en');
        });
    }

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

    public function majors()
    {
        return $this->hasMany(Major::class, 'id_parent');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tbl_division_subject', 'id_division', 'id_subject');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'id_division');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'id_division');
    }
}
