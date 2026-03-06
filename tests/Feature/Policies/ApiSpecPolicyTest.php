<?php

use App\Models\ApiSpec;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $permissions = [
        'spec.view.list',
        'spec.view.detail',
        'spec.generate.spec',
        'spec.delete.spec',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(
        ['name' => 'test-spec-developer', 'guard_name' => 'web'],
        ['display_name' => 'Test Spec Developer'],
    );

    $role->syncPermissions($permissions);

    $this->developerRole = $role;
});

it('allows users with view permission to view specs', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $spec = ApiSpec::factory()->create();

    expect($user->can('viewAny', ApiSpec::class))->toBeTrue()
        ->and($user->can('view', $spec))->toBeTrue();
});

it('denies users without view permission from viewing specs', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', ApiSpec::class))->toBeFalse();
});

it('allows users with generate permission to create specs', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    expect($user->can('create', ApiSpec::class))->toBeTrue();
});

it('allows users with delete permission to delete specs', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $spec = ApiSpec::factory()->create();

    expect($user->can('delete', $spec))->toBeTrue();
});

it('denies restore and force delete', function () {
    $user = User::factory()->create();
    $user->assignRole($this->developerRole);

    $spec = ApiSpec::factory()->create();

    expect($user->can('restore', $spec))->toBeFalse()
        ->and($user->can('forceDelete', $spec))->toBeFalse();
});
