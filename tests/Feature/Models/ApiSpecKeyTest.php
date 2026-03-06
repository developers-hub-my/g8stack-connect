<?php

declare(strict_types=1);

use App\Models\ApiSpec;
use App\Models\ApiSpecKey;

it('can be created with factory', function () {
    $key = ApiSpecKey::factory()->create();

    expect($key)->toBeInstanceOf(ApiSpecKey::class)
        ->and($key->name)->toBeString()
        ->and($key->key_hash)->toBeString()
        ->and($key->key_prefix)->toBeString();
});

it('belongs to an api spec', function () {
    $key = ApiSpecKey::factory()->create();

    expect($key->apiSpec)->toBeInstanceOf(ApiSpec::class);
});

it('generates keys with g8c_ prefix', function () {
    $key = ApiSpecKey::generateKey();

    expect($key)->toStartWith('g8c_')
        ->and(strlen($key))->toBe(44);
});

it('hashes keys consistently', function () {
    $plainKey = 'g8c_test123';
    $hash1 = ApiSpecKey::hashKey($plainKey);
    $hash2 = ApiSpecKey::hashKey($plainKey);

    expect($hash1)->toBe($hash2)
        ->and($hash1)->not->toBe($plainKey);
});

it('extracts key prefix', function () {
    $plainKey = 'g8c_abcdefghij';
    $prefix = ApiSpecKey::prefixFromKey($plainKey);

    expect($prefix)->toBe('g8c_abcdefgh');
});

it('detects expired keys', function () {
    $expired = ApiSpecKey::factory()->expired()->create();
    $valid = ApiSpecKey::factory()->create();

    expect($expired->isExpired())->toBeTrue()
        ->and($valid->isExpired())->toBeFalse();
});

it('checks ip allowlist', function () {
    $key = ApiSpecKey::factory()->withIpRestriction(['192.168.1.1', '10.0.0.1'])->create();

    expect($key->isIpAllowed('192.168.1.1'))->toBeTrue()
        ->and($key->isIpAllowed('10.0.0.1'))->toBeTrue()
        ->and($key->isIpAllowed('172.16.0.1'))->toBeFalse();
});

it('allows all ips when allowlist is empty', function () {
    $key = ApiSpecKey::factory()->create(['allowed_ips' => null]);

    expect($key->isIpAllowed('any.ip.here.ok'))->toBeTrue();
});

it('hides key_hash from serialization', function () {
    $key = ApiSpecKey::factory()->create();

    $array = $key->toArray();

    expect($array)->not->toHaveKey('key_hash')
        ->and($array)->not->toHaveKey('id');
});
