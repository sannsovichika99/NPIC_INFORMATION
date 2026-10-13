<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Storage;

class Student extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_student';
    protected $primaryKey = 'id_student';

    protected $casts = [
        'id_student' => 'int',
        'id_gender' => 'int',
        'id_nationality' => 'int',
        'id_ethnicity' => 'int',
        'id_religion' => 'int',
        'id_pob' => 'int',
        'id_por' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    protected $fillable = [
        'student_code',
        'name_en',
        'name_kh',
        'dob',
        'job',
        'home_no',
        'street_no',
        'phone',
        'photo',
        'id_gender',
        'id_nationality',
        'id_ethnicity',
        'id_religion',
        'id_pob',
        'id_por',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->orderBy('tbl_student.name_en');
        });
    }

    public function scopeSearchKeyword(Builder $query, string $keyword): void
    {
        $query->where('name_kh', 'like', '%' . $keyword . '%')
            ->orWhere('name_en', 'like', '%' . $keyword . '%')
            ->orWhere('phone', 'like', '%' . $keyword . '%')
            ->orWhere('student_code', 'like', "%{$keyword}%");
    }

    protected function NameEn(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => strtoupper($value),
            set: fn(string $value) => strtoupper($value),
        );
    }

    protected function dob(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    protected function getHasDetailsAttribute()
    {
        return $this->father()->exists()
            && $this->mother()->exists();
    }

    protected function getHasSpouseAttribute()
    {
        return $this->spouse()->exists();
    }

    protected function photo5x5(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/students/5x5/{$this->photo}") : null
        );
    }

    protected function photo3x4(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/students/3x4/{$this->photo}") : null
        );
    }

    protected function photo4x6(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/students/4x6/{$this->photo}") : null
        );
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->photo ? Storage::disk('public')->url("images/students/thumbnails/{$this->photo}") : null
        );
    }

    public function placeOfBirth()
    {
        return $this->belongsTo(Geography::class, 'id_pob');
    }

    public function placeOfResidence()
    {
        return $this->belongsTo(Geography::class, 'id_por');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'id_nationality');
    }

    public function ethnicity()
    {
        return $this->belongsTo(Ethnicity::class, 'id_ethnicity');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'id_religion');
    }

    public function geography()
    {
        return $this->belongsTo(Geography::class, 'id_geography');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function spouse()
    {
        return $this->hasOne(Spouse::class, 'id_student');
    }

    public function father()
    {
        return $this->hasOne(Father::class, 'id_student');
    }

    public function mother()
    {
        return $this->hasOne(Mother::class, 'id_student');
    }

    public function siblings()
    {
        return $this->hasMany(Sibling::class, 'id_student');
    }

    public function custodian()
    {
        return $this->hasOne(Custodian::class, 'id_student');
    }

    public function student_enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'id_student');
    }
}
