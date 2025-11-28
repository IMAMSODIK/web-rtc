<?php

namespace App\Policies;

use App\Models\MockTestSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MockTestSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MockTestSession $mockTestSession)
    {
        return $user->id === $mockTestSession->student_id || 
               $user->id === $mockTestSession->teacher_id;
    }

    /**
     * Determine whether the user can create models.
     */
     public function update(User $user, MockTestSession $mockTestSession)
    {
        return $user->id === $mockTestSession->teacher_id;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MockTestSession $mockTestSession)
    {
        return $user->id === $mockTestSession->student_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MockTestSession $mockTestSession): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MockTestSession $mockTestSession): bool
    {
        return false;
    }
}
