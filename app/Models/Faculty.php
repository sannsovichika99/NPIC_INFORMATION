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
class Faculty extends Model
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
            $builder->where('label', 'Faculty')
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

    public function departments()
    {
        return $this->hasMany(Department::class, 'id_parent');
    }

    public function majors()
    {
        return $this->hasManyThrough(
            Division::class,
            Department::class,
            'id_parent', // Foreign key on the tbl_department table
            'tbl_major.id_parent', // Foreign key on the tbl_major table
            'id_division', // Local key on the tbl_faculty table
            'id_division' // Local key on the tbl_department table
        )->select('tbl_major.*')
            ->from('tbl_division as tbl_major')
            ->where('tbl_division.label', 'Department')
            ->where('tbl_major.label', 'Major');
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
