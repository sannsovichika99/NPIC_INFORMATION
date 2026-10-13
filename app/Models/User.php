<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\TimeStamps;
use App\Traits\TracksCreator;
use App\Traits\TracksUpdater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, Notifiable, TimeStamps, TracksCreator, TracksUpdater;

	protected $table = 'tbl_user';
	protected $primaryKey = 'id_user';
	protected $appends = ['is_master'];

	protected $casts = [
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $hidden = [
		'password',
	];

	protected $fillable = [
		'user_label',
		'email',
		'username',
		'password',
		'acc_status',
		'act_status',
		'id_employee',
		'created_by',
		'updated_by'
	];
	protected function getIsMasterAttribute()
	{
		return UserPermission::where('id_user', $this->id_user)->where('id_permission', 1)->first() ? true : false;
	}
	public function scopeManageable(Builder $query, $operator): void
	{
		$query->where('id_user', '<>', $operator->id_user)
			->when($operator->is_master, function ($q) {
				$q->whereDoesntHave('user_permissions', function ($q) {
					$q->where('id_permission', 1);
				});
			})
			->when(!$operator->is_master, function ($q) {
				$q->whereDoesntHave('user_permissions', function ($q) {
					$q->where('id_permission', '<=', 2);
				});
			});
	}
	public function scopeEnabled(Builder $query): void
	{
		$query->where('acc_status', 'ENABLED');
	}
	public function scopeDisabled(Builder $query): void
	{
		$query->where('acc_status', 'DISABLED');
	}
	public function scopeAllowed(Builder $query): void
	{
		$query->where('act_status', 'ALLOWED');
	}
	public function scopeDenied(Builder $query): void
	{
		$query->where('acc_status', 'DENIED');
	}
	public function scopeValidPermission0(Builder $query, $id_user, $id_permission): void
	{
		if (!Permission::where('id_permission', $id_permission)->Enabled()->first()) {
			$query->whereRaw('1 = 0');
		} else {
			$query->where('id_user', $id_user)
				->Enabled()
				->whereHas('user_permissions.permission', function ($q) use ($id_permission) {
					$q->Enabled()
						->where(function ($q) use ($id_permission) {
							$q->where('id_permission', 1)
								->orWhere('id_permission', $id_permission);
						});
				});
		}
	}
	public function scopeValidPermission1_1(Builder $query, $id_user, $id_permission, $id_division): void
	{
		if (!Permission::where('id_permission', $id_permission)->where('type', '1-1')->Enabled()->first()) {
			$query->whereRaw('1 = 0');
		} else {
			$query->where('id_user', $id_user)
				->Enabled()
				->where(function ($q) use ($id_permission, $id_division) {
					$q->whereHas('user_permissions', function ($q) use ($id_permission, $id_division) {
						$q->whereHas('permission', function ($q) use ($id_permission) {
							$q->where('id_permission', $id_permission)
								->Enabled();
						})->whereHas('user_permission_details', function ($q) use ($id_division) {
							$q->whereHas('division', function ($q) use ($id_division) {
								$q->where('id_division', $id_division)
								->Enabled();
							})->whereNull('id_shift');
						});
					})->orWhereHas('user_permissions.permission', function ($q) {
						$q->where('id_permission', 1)
							->Enabled();
					});
				});
		}
	}
	public function scopeValidPermission1_2(Builder $query, $id_user, $id_permission, $id_shift): void
	{
		if (!Permission::where('id_permission', $id_permission)->where('type', '1-2')->Enabled()->first()) {
			$query->whereRaw('1 = 0');
		} else {
			$query->where('id_user', $id_user)
				->Enabled()
				->where(function ($q) use ($id_permission, $id_shift) {
					$q->whereHas('user_permissions', function ($q) use ($id_permission, $id_shift) {
						$q->whereHas('permission', function ($q) use ($id_permission) {
							$q->where('id_permission', $id_permission)
								->Enabled();
						})->whereHas('user_permission_details', function ($q) use ($id_shift) {
							$q->whereNull('id_division')
								->where('id_shift', $id_shift);
						});
					})->orWhereHas('user_permissions.permission', function ($q) {
						$q->where('id_permission', 1)
							->Enabled();
					});
				});
		}
	}
	public function scopeValidPermission2(Builder $query, $id_user, $id_permission, $id_division, $id_shift): void
	{
		if (!Permission::where('id_permission', $id_permission)->where('type', '2')->Enabled()->first()) {
			$query->whereRaw('1 = 0');
		} else {
			$query->where('id_user', $id_user)
				->Enabled()
				->where(function ($q) use ($id_permission, $id_division, $id_shift) {
					$q->whereHas('user_permissions', function ($q) use ($id_permission, $id_division, $id_shift) {
						$q->whereHas('permission', function ($q) use ($id_permission) {
							$q->where('id_permission', $id_permission)
								->Enabled();
						})->whereHas('user_permission_details', function ($q) use ($id_division, $id_shift) {
							$q->whereHas('division', function ($q) use ($id_division) {
								$q->where('id_division', $id_division)
								->Enabled();
							})
								->where('id_shift', $id_shift);
						});
					})->orWhereHas('user_permissions.permission', function ($q) {
						$q->where('id_permission', 1)
							->Enabled();
					});
				});
		}
	}

	public function scopeScheduleUsersByRoom(Builder $query, $id_room, $id_academic_calendar): void
	{
		$query
			->whereHas('schedules_updated_by', function ($q) use ($id_room, $id_academic_calendar) {
				$q->where('id_academic_calendar', $id_academic_calendar)
					->where('id_room', $id_room);
			});
	}

	public function scopeScheduleUsersByGroup(Builder $query, $id_group, $id_academic_calendar): void
	{
		$query
			->whereHas('schedules_updated_by', function ($q) use ($id_group, $id_academic_calendar) {
				$q->where('id_academic_calendar', $id_academic_calendar)
					->whereHas('groups', function ($q) use ($id_group) {
						$q->where('tbl_group.id_group', $id_group);
					});
			});
	}

	public function scopeScheduleUsersByEmployee(Builder $query, $id_employee, $id_academic_calendar): void
	{
		$query
			->whereHas('schedules_updated_by', function ($q) use ($id_employee, $id_academic_calendar) {
				$q->where('id_academic_calendar', $id_academic_calendar)
					->whereHas('employees', function ($q) use ($id_employee) {
						$q->where('tbl_employee.id_employee', $id_employee);
					});
			});
	}

	public function linked_employee()
	{
		return $this->belongsTo(Employee::class, 'id_employee');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updater()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function user_permissions()
	{
		return $this->hasMany(UserPermission::class, 'id_user');
	}

	public function schedules_created_by()
	{
		return $this->hasMany(Schedule::class, 'created_by');
	}

	public function schedules_updated_by()
	{
		return $this->hasMany(Schedule::class, 'updated_by');
	}

	public function schedules_approved_by()
	{
		return $this->hasMany(Schedule::class, 'approved_by');
	}
}
