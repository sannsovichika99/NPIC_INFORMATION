<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Session extends Model
{
    protected $table = 'tbl_session';
    protected $primaryKey = 'id_session';
    public $timestamps = false;

    protected $fillable = [
        'session'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->orderBy('session', 'asc');
        });
    }

    public function scopeByShift(Builder $query, $id_shift): void
    {
        $query->whereHas('session_details', function ($q) use ($id_shift) {
            $q->where('id_shift', $id_shift);
        });
    }

    public function session_details()
    {
        return $this->hasMany(SessionDetail::class, 'id_session');
    }
}
