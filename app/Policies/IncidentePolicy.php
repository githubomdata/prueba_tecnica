<?php

namespace App\Policies;

use App\Models\Incidente;
use App\Models\User;

class IncidentePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incidente $incidente): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->puede_editar;
    }

    public function update(User $user, Incidente $incidente): bool
    {
        return $user->puede_editar;
    }
}
