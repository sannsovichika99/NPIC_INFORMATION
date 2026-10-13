<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SessionDetail extends Model
{
    protected $table = 'tbl_session_detail';
    protected $primaryKey = 'id_session_detail';
    public $timestamps = false;

    protected $casts = [
        'id_day' => 'int',
        'id_session' => 'int',
        'id_shift' => 'int'
    ];

    protected $fillable = [
        'session_label',
        'id_day',
        'id_session',
        'id_shift'
    ];

    public function day()
    {
        return $this->belongsTo(Day::class, 'id_day');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'id_session');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_session_detail');
    }
}
