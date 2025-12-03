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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Both teacher and student can view the session
     */
    public function view(User $user, MockTestSession $mockTestSession): bool
    {
        return $user->id === $mockTestSession->student_id ||
               $user->id === $mockTestSession->teacher_id;
    }

    /**
     * Determine whether the user can create models.
     * Only students can create sessions
     */
    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    /**
     * Determine whether the user can update the model.
     * Teacher can update (accept/reject), both can end session
     */
    public function update(User $user, MockTestSession $mockTestSession): bool
    {
        return $user->id === $mockTestSession->teacher_id;
    }

    /**
     * Determine whether the user can end the session.
     * Both teacher and student can end the session
     */
    public function endSession(User $user, MockTestSession $mockTestSession): bool
    {
        return $user->id === $mockTestSession->student_id ||
               $user->id === $mockTestSession->teacher_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only student can delete their own pending session
     */
    public function delete(User $user, MockTestSession $mockTestSession): bool
    {
        return $user->id === $mockTestSession->student_id &&
               $mockTestSession->status === 'pending';
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
