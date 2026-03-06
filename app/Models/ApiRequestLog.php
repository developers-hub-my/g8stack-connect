<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    public $timestamps = false;

    protected $table = 'api_request_logs';

    protected $hidden = ['id'];

    // Disable auditing for log table (prevent recursion)
    protected $auditExclude = ['*'];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'latency_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function apiSpec(): BelongsTo
    {
        return $this->belongsTo(ApiSpec::class);
    }

    public function apiSpecKey(): BelongsTo
    {
        return $this->belongsTo(ApiSpecKey::class);
    }
}
