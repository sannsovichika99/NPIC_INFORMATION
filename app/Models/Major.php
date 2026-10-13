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
class Major extends Model
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
            $builder->where('label', 'Major')
                ->orderBy('name_en');
        });
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED')
            ->where(function ($q) {
                $q->whereHas('center', function ($q) {
                    $q->Enabled();
                })->orWhereHas('department', function ($q) {
                    $q->Enabled();
                });
            });
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED')
            ->orWhere(function ($q) {
                $q->whereHas('center', function ($q) {
                    $q->Disabled();
                })->orWhereHas('department', function ($q) {
                    $q->Disabled();
                });
            });
    }

    public function parent()
    {
        return $this->belongsTo(Division::class, 'id_parent');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_parent');
    }

    public function center()
    {
        return $this->belongsTo(Center::class, 'id_parent');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'tbl_division_subject', 'id_division', 'id_subject');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'id_division');
    }
}
