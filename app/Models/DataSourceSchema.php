<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataSourceSchema extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'primary_keys' => 'array',
            'indexes' => 'array',
        ];
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }
}
