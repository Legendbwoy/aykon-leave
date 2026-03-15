<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any attendance records.
     */
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the attendance record.
     */
    public function view(User $user, Attendance $attendance)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $user->employee->department_id === $attendance->employee->department_id;
        }

        return $user->employee->id === $attendance->employee_id;
    }

    /**
     * Determine whether the user can create attendance records.
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the attendance record.
     */
    public function update(User $user, Attendance $attendance)
    {
        return $user->isAdmin() || 
               ($user->isManager() && $user->employee->department_id === $attendance->employee->department_id);
    }

    /**
     * Determine whether the user can delete the attendance record.
     */
    public function delete(User $user, Attendance $attendance)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export attendance records.
     */
    public function export(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can view attendance summary.
     */
    public function viewSummary(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }
}