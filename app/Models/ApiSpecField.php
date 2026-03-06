<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiSpecField extends Model
{
    use SoftDeletes;

    protected $table = 'api_spec_fields';

    protected $hidden = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_exposed' => 'boolean',
            'is_pii' => 'boolean',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_sortable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function apiSpec(): BelongsTo
    {
        return $this->belongsTo(ApiSpec::class);
    }
}
