<?php

declare(strict_types=1);

use App\Services\ApiRuntime\ResourceNameSuggester;

it('strips common table prefixes', function () {
    $suggester = new ResourceNameSuggester;

    expect($suggester->suggest('tbl_employees'))->toBe('employees')
        ->and($suggester->suggest('tb_departments'))->toBe('departments')
        ->and($suggester->suggest('t_users'))->toBe('users')
        ->and($suggester->suggest('vw_active_users'))->toBe('active_users')
        ->and($suggester->suggest('sys_config'))->toBe('configs');
});

it('strips version suffixes', function () {
    $suggester = new ResourceNameSuggester;

    expect($suggester->suggest('emp_records_v2'))->toBe('emp_records')
        ->and($suggester->suggest('users_v1'))->toBe('users');
});

it('pluralises singular names', function () {
    $suggester = new ResourceNameSuggester;

    expect($suggester->suggest('employee'))->toBe('employees')
        ->and($suggester->suggest('department'))->toBe('departments');
});

it('handles already plural names', function () {
    $suggester = new ResourceNameSuggester;

    expect($suggester->suggest('employees'))->toBe('employees')
        ->and($suggester->suggest('users'))->toBe('users');
});

it('suggests field names by stripping column prefixes', function () {
    $suggester = new ResourceNameSuggester;

    expect($suggester->suggestFieldName('fld_email'))->toBe('email')
        ->and($suggester->suggestFieldName('col_name'))->toBe('name')
        ->and($suggester->suggestFieldName('emp_full_name'))->toBe('full_name');
});

it('suggests multiple table names without collision', function () {
    $suggester = new ResourceNameSuggester;

    $result = $suggester->suggestMany(['tbl_users', 'tbl_roles', 'tbl_permissions']);

    expect($result)->toHaveCount(3)
        ->and($result['tbl_users'])->toBe('users')
        ->and($result['tbl_roles'])->toBe('roles')
        ->and($result['tbl_permissions'])->toBe('permissions');
});
