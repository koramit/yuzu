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
        if ($user->hasRole('md')) {
            return $visit->status !== 'appointment';
        } elseif ($user->hasRole('nurse')) {
            return $visit->status === 'screen' || $visit->status === 'appointment';
        }

        return false;
    }

    public function discharge(User $user, Visit $visit)
    {
        if (collect(['canceled', 'discharged'])->contains($visit->status)) {
            return false;
        }
        if ($visit->authorized_at && $visit->attached_opd_card_at) {
            if ($user->hasRole('md')) {
                return true;
            } elseif ($user->hasRole('nurse')) { // nurse discharge from swab
                return $visit->status === 'enqueue_swab';
            }
        } elseif ($user->hasRole('md') && ! $visit->swabbed) {
            return true;
        }

        return false;
    }

    public function replace(User $user, Visit $visit)
    {
        if (! $visit->ready_to_print || ! $user->can('replace_visit')) {
            return false;
        }
        if ($user->hasRole('md')) {
            return true;
        } elseif ($user->hasRole('nurse')) {
            return $visit->form['md']['signed_on_behalf'];
        }

        return false;
    }

    public function view(User $user, Visit $visit)
    {
        return $visit->ready_to_print || $user->can('evaluate');
    }

    public function cancel(User $user, Visit $visit)
    {
        return $user->hasRole('admin') ||
            ($user->can('cancel_visit') && collect(['screen', 'exam'])->contains($visit->status) && ! $visit->swabbed);
    }

    public function authorize(User $user, Visit $visit)
    {
        return $user->can('authorize_visit')
            && $visit->patient_id
            && $visit->enqueued_at
            && $visit->status !== 'canceled';
    }

    public function attachOPDCard(User $user, Visit $visit)
    {
        return $user->can('attach_opd_card')
            && $visit->patient_id
            && $visit->enqueued_at
            && $visit->status !== 'canceled';
    }

    public function printOPDCard(User $user, Visit $visit)
    {
        return $user->can('print_opd_card') && $visit->ready_to_print;
    }

    public function queue(User $user, Visit $visit)
    {
        return $user->can('authorize_visit')
            && $visit->patient_id
            && $visit->status !== 'canceled';
    }

    public function fillHn(User $user, Visit $visit)
    {
        return $user->can('authorize_visit') && ! $visit->patient_id && $visit->status !== 'canceled';
    }

    public function refer(User $user, Visit $visit)
    {
        return $user->can('evaluate') && $user->mocktail_token;
    }
}
