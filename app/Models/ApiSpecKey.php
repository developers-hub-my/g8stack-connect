<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiSpecKey extends Model
{
    use SoftDeletes;

    protected $table = 'api_spec_keys';

    protected $hidden = ['id', 'key_hash'];

    protected function casts(): array
    {
        return [
            'rate_limit' => 'integer',
            'allowed_ips' => 'array',
            'allowed_origins' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function apiSpec(): BelongsTo
    {
        return $this->belongsTo(ApiSpec::class);
    }

    public static function generateKey(): string
    {
        return 'g8c_'.Str::random(40);
    }

    public static function hashKey(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    public static function prefixFromKey(string $plainKey): string
    {
        return substr($plainKey, 0, 12);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips);
    }

    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
