<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Permission extends Model
{
    protected $table = 'tbl_permission';
    protected $primaryKey = 'id_permission';
    public $timestamps = false;

    protected $fillable = [
        'permission',
        'type',
        'record_status'
    ];

    public function scopeEnabled(Builder $query): void
    {
        $query->where('record_status', 'ENABLED');
    }
    public function scopeDisabled(Builder $query): void
    {
        $query->where('record_status', 'DISABLED');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tbl_user_permission', 'id_permission', 'id_user');
    }
    public function user_permissions()
    {
        return $this->hasMany(UserPermission::class, 'id_permission');
    }
}
