<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Visit $visit)
    {
        if ($user->role_names->contains('md')) {
            return true;
        // except printed
        } elseif ($user->role_names->contains('nurse')) {
            if ($visit->status === 'screen') {
                return true;
            } else {
                return false;
                // except sign on behalf
            }
        }
    }

    public function discharge(User $user, Visit $visit)
    {
        if ($user->role_names->contains('md')) {
            return true;
        } elseif ($user->role_names->contains('nurse')) {
            return $visit->submitted_at ? true : false;
        }
    }
}
