<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\StudentEnrollmentRecordStatusEnum;
use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use TimeStamps, TracksCreator, TracksUpdater;

    protected $table = 'tbl_student_enrollment';
    protected $primaryKey = 'id_student_enrollment';

    protected $casts = [
        'id_student' => 'int',
        'id_academic_group' => 'int',
        'record_status' => StudentEnrollmentRecordStatusEnum::class,
        'created_by' => 'int',
        'updated_by' => 'int',
    ];

    protected $fillable = [
        'id_student',
        'id_academic_group',
        'record_status',
        'dorm_status',
        'status_reason',
        'status_date',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('boot', function (Builder $builder) {
            $builder->addSelect('tbl_student_enrollment.*')
                ->leftJoin('tbl_student', 'tbl_student.id_student', '=', 'tbl_student_enrollment.id_student')
                ->orderBy('tbl_student.name_en');
        });
    }

    protected function statusDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function academic_group()
    {
        return $this->belongsTo(AcademicGroup::class, 'id_academic_group');
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

    public function student_scores()
    {
        return $this->hasMany(StudentScore::class, 'id_student_enrollment');
    }
}
