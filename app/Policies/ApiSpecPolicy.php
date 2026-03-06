<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApiSpec;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ApiSpecPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('spec.view.list');
    }

    public function view(User $user, ApiSpec $apiSpec): Response|bool
    {
        return $user->can('spec.view.detail');
    }

    public function create(User $user): Response|bool
    {
        return $user->can('spec.generate.spec');
    }

    public function update(User $user, ApiSpec $apiSpec): Response|bool
    {
        return $user->can('spec.generate.spec');
    }

    public function delete(User $user, ApiSpec $apiSpec): Response|bool
    {
        return $user->can('spec.delete.spec');
    }

    public function restore(User $user, ApiSpec $apiSpec): Response|bool
    {
        return false;
    }

    public function forceDelete(User $user, ApiSpec $apiSpec): Response|bool
    {
        return false;
    }
}
