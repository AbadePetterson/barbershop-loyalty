<?php

namespace App\Policies;

use App\Models\client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class clientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {

    }

    public function view(User $user, client $client)
    {
    }

    public function create(User $user)
    {
    }

    public function update(User $user, client $client)
    {
    }

    public function delete(User $user, client $client)
    {
    }

    public function restore(User $user, client $client)
    {
    }

    public function forceDelete(User $user, client $client)
    {
    }
}
