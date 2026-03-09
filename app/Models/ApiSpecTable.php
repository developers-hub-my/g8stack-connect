<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiSpecTable extends Model
{
    use SoftDeletes;

    protected $table = 'api_spec_tables';

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'operations' => 'array',
            'configuration' => 'array',
            'sort_order' => 'integer',
            'sql_parameters' => 'array',
            'result_columns' => 'array',
        ];
    }

    public function isSqlQuery(): bool
    {
        return ! empty($this->sql_query);
    }

    public function apiSpec(): BelongsTo
    {
        return $this->belongsTo(ApiSpec::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ApiSpecField::class)->orderBy('sort_order');
    }

    public function getDefaultOperations(): array
    {
        return [
            'list' => true,
            'show' => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }

    public function isOperationAllowed(string $operation): bool
    {
        $operations = $this->operations ?? $this->getDefaultOperations();

        return (bool) ($operations[$operation] ?? false);
    }
}
