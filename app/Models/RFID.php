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
class RFID extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;
    protected $table = 'tbl_rfid';
    protected $primaryKey = 'id_rfid';

    protected $casts = [
        'id_student' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'rfid_code',
        'id_student',
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

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
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
