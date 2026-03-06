<?php

use App\Models\DataSource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $permissions = [
        'datasource.view.list',
        'datasource.connect.source',
        'datasource.introspect.source',
        'datasource.preview.data',
        'datasource.update.source',
        'datasource.delete.source',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(
        ['name' => 'test-developer', 'guard_name' => 'web'],
        ['display_name' => 'Test Developer'],
    );

    $role->syncPermissions($permissions);

    $this->developerRole = $role;
});

it('allows users with view permission to view data sources', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $dataSource = DataSource::factory()->create();

    expect($user->can('viewAny', DataSource::class))->toBeTrue()
        ->and($user->can('view', $dataSource))->toBeTrue();
});

it('denies users without view permission from viewing data sources', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', DataSource::class))->toBeFalse();
});

it('allows users with connect permission to create data sources', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    expect($user->can('create', DataSource::class))->toBeTrue();
});

it('allows users with introspect permission to introspect', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $dataSource = DataSource::factory()->create();

    expect($user->can('introspect', $dataSource))->toBeTrue();
});

it('allows users with preview permission to preview data', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $dataSource = DataSource::factory()->create();

    expect($user->can('preview', $dataSource))->toBeTrue();
});

it('denies restore and force delete', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $dataSource = DataSource::factory()->create();

    expect($user->can('restore', $dataSource))->toBeFalse()
        ->and($user->can('forceDelete', $dataSource))->toBeFalse();
});
