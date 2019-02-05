<?php


namespace App\Policies;


use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * Runs before the policy, checking for a null user.
     *
     * @param User $user
     * @return bool|null
     */
    public function before(User $user)
    {
        if (is_null($user)) {
            return false;
        }

        /*
         * Return null so further policy methods can be called
         * If we returned true here, it would consider it authenticated.
         */
        return null;
    }

    /**
     * Determines if a user owns a model.
     *
     * @param User $user
     * @param string $userId
     * @return bool
     */
    protected function owns(User $user, $userId)
    {
        return (int) $user->id === (int) $userId;
    }
}