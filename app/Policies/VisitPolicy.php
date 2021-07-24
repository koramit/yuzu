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
        if ($visit->ready_to_print) {
            return false;
        }

        if ($user->isRole('md')) {
            return true;
        // except printed
        } elseif ($user->isRole('nurse')) {
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
        if ($user->isRole('md')) {
            return true;
        } elseif ($user->isRole('nurse')) { // nurse discharge from swab
            return $visit->submitted_at ? true : false;
        }
    }

    public function replace(User $user, Visit $visit)
    {
        if (! $visit->ready_to_print || ! $user->can('replace_visit')) {
            return false;
        }

        if ($user->isRole('md')) {
            return true;
        } elseif ($user->isRole('nurse')) {
            return $visit->form['md']['signed_on_behalf'];
        }

        return true;
    }

    public function view(User $user, Visit $visit)
    {
        return true;

        return $visit->ready_to_print;
    }
}
