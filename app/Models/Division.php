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
class Division extends Model
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

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED');
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED');
    }

    public function scopeFaculty(Builder $query): void
    {
        $query->where('label', 'Faculty')->whereNull('id_parent');
    }
    public function scopeCenter(Builder $query): void
    {
        $query->where('label', 'Center')->whereNull('id_parent');
    }
    public function scopeOffice(Builder $query): void
    {
        $query->where('label', 'Office')->whereNull('id_parent');
    }
    public function scopeDepartment(Builder $query): void
    {
        $query->where('label', 'Department');
    }
    public function scopeMajor(Builder $query): void
    {
        $query->where('label', 'Major');
    }

    public function parent()
    {
        return $this->belongsTo(Division::class, 'id_parent');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function divisions()
    {
        return $this->hasMany(Division::class, 'id_parent');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tbl_division_subject', 'id_division', 'id_subject');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'id_division');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'id_division');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'id_division');
    }

    public function user_permission_details()
    {
        return $this->hasMany(UserPermissionDetail::class, 'id_division');
    }
}
