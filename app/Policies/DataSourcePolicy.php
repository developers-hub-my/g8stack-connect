<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DataSource;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class DataSourcePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('datasource.view.list');
    }

    public function view(User $user, DataSource $dataSource): Response|bool
    {
        return $user->can('datasource.view.list');
    }

    public function create(User $user): Response|bool
    {
        return $user->can('datasource.connect.source');
    }

    public function update(User $user, DataSource $dataSource): Response|bool
    {
        return $user->can('datasource.update.source');
    }

    public function delete(User $user, DataSource $dataSource): Response|bool
    {
        return $user->can('datasource.delete.source');
    }

    public function restore(User $user, DataSource $dataSource): Response|bool
    {
        return false;
    }

    public function forceDelete(User $user, DataSource $dataSource): Response|bool
    {
        return false;
    }

    public function introspect(User $user, DataSource $dataSource): Response|bool
    {
        return $user->can('datasource.introspect.source');
    }

    public function preview(User $user, DataSource $dataSource): Response|bool
    {
        return $user->can('datasource.preview.data');
    }
}
