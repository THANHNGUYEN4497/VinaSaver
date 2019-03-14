<?php

namespace App\Policies;

use App\User;
use App\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\HandlesPermission;
use Illuminate\Auth\Access\AuthorizationException;

class StaffPolicy
{
    use HandlesAuthorization;



    /**
     * Determine whether the user can view the staff.
     *
     * @param  \App\User  $user
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function view(Staff $user)
    {
        return true;
    }

    /**
     * Determine whether the user can create staff.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(Staff $user)
    {
        if ($user->privilege == 1) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the staff.
     *
     * @param  \App\User  $user
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function update(Staff $user)
    {
        if ($user->privilege == 1) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the staff.
     *
     * @param  \App\User  $user
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function delete(Staff $user)
    {
        if ($user->privilege == 1) {
            return true;
        }
        return false;
    }

    public function updateCompany(Staff $user)
    {
        if ($user->privilege == 1) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the staff.
     *
     * @param  \App\User  $user
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function restore(Staff $user, Staff $staff)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the staff.
     *
     * @param  \App\User  $user
     * @param  \App\Staff  $staff
     * @return mixed
     */
    public function forceDelete(Staff $user, Staff $staff)
    {
        //
    }

    public function listByStaff(Staff $user)
    {
        if ($user->privilege == 1) {
            return true;
        }
        return false;
    }
}
