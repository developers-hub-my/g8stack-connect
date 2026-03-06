<?php

use App\Services\PiiDetection\PiiDetectionService;

it('flags known sensitive column names', function () {
    $service = new PiiDetectionService;

    $result = $service->scan(['id', 'name', 'password', 'ic_number', 'email']);

    expect($result->flagged)->toContain('password', 'ic_number')
        ->and($result->safe)->toContain('id', 'name', 'email');
});

it('is case insensitive', function () {
    $service = new PiiDetectionService;

    $result = $service->scan(['Password', 'SECRET', 'Api_Key']);

    expect($result->flagged)->toContain('Password', 'SECRET', 'Api_Key');
});

it('handles array column format', function () {
    $service = new PiiDetectionService;

    $columns = [
        ['name' => 'id', 'type' => 'bigint'],
        ['name' => 'credit_card', 'type' => 'varchar'],
        ['name' => 'name', 'type' => 'varchar'],
    ];

    $result = $service->scan($columns);

    expect($result->flagged)->toContain('credit_card')
        ->and($result->safe)->toContain('id', 'name');
});

it('returns matched patterns', function () {
    $service = new PiiDetectionService;

    $result = $service->scan(['user_password_hash', 'nric_number']);

    expect($result->patterns)->toHaveKey('user_password_hash')
        ->and($result->patterns['user_password_hash'])->toBe('password');
});

it('returns empty results for safe columns', function () {
    $service = new PiiDetectionService;

    $result = $service->scan(['id', 'name', 'email', 'created_at']);

    expect($result->flagged)->toBeEmpty()
        ->and($result->safe)->toHaveCount(4);
});

it('flags partial matches', function () {
    $service = new PiiDetectionService;

    $result = $service->scan(['bank_account_number', 'user_ssn_encrypted']);

    expect($result->flagged)->toContain('bank_account_number', 'user_ssn_encrypted');
});
