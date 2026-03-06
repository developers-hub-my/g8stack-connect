<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiSpecVersion extends Model
{
    protected $table = 'api_spec_versions';

    protected $hidden = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'openapi_spec' => 'array',
            'configuration' => 'array',
        ];
    }

    public function apiSpec(): BelongsTo
    {
        return $this->belongsTo(ApiSpec::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
