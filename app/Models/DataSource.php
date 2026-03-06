<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataSource extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'id',
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'type' => DataSourceType::class,
            'status' => ConnectionStatus::class,
            'credentials' => 'encrypted:array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schemas(): HasMany
    {
        return $this->hasMany(DataSourceSchema::class);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ApiSpec::class);
    }

    public function connectionAudits(): HasMany
    {
        return $this->hasMany(ConnectionAudit::class);
    }
}
