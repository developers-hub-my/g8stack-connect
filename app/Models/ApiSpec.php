<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SpecStatus;
use App\Enums\WizardMode;
use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiSpec extends Model
{
    use SoftDeletes;

    protected $table = 'api_specs';

    protected $hidden = [
        'id',
    ];

    protected static function booted(): void
    {
        static::creating(function (ApiSpec $spec) {
            if (empty($spec->slug) && ! empty($spec->name)) {
                $spec->slug = Str::slug($spec->name);

                $original = $spec->slug;
                $counter = 1;
                while (static::withTrashed()->where('slug', $spec->slug)->exists()) {
                    $spec->slug = $original.'-'.$counter;
                    $counter++;
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'wizard_mode' => WizardMode::class,
            'status' => SpecStatus::class,
            'openapi_spec' => 'array',
            'selected_tables' => 'array',
            'configuration' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ApiSpecField::class)->orderBy('sort_order');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(ApiSpecTable::class)->orderBy('sort_order');
    }

    public function keys(): HasMany
    {
        return $this->hasMany(ApiSpecKey::class);
    }

    public function isGrouped(): bool
    {
        return $this->tables()->count() > 1;
    }

    public function resolveTable(string $resourceName): ?ApiSpecTable
    {
        return $this->tables()->where('resource_name', $resourceName)->first();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ApiSpecVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): ?ApiSpecVersion
    {
        return $this->versions()->first();
    }
}
