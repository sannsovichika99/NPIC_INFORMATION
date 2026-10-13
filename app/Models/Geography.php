<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geography extends Model
{
    protected $table = 'tbl_geography';
    protected $primaryKey = 'id_geography';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'id_geography' => 'int',
        'id_parent' => 'int'
    ];

    protected $fillable = [
        'id_geography',
        'name_en',
        'name_kh',
        'name_latin',
        'unit_en',
        'unit_kh',
        'unit_latin',
        'id_parent'
    ];

    public function parent()
    {
        return $this->belongsTo(Geography::class, 'id_parent');
    }

    public function children()
    {
        return $this->hasMany(Geography::class, 'id_parent');
    }
}
