<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSource;

use App\Enums\DataSourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('datasource.connect.source') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(DataSourceType::class)],
            'credentials' => ['required', 'array'],
            'credentials.database' => ['required', 'string'],
            'credentials.host' => ['nullable', 'string'],
            'credentials.port' => ['nullable', 'integer'],
            'credentials.username' => ['nullable', 'string'],
            'credentials.password' => ['nullable', 'string'],
        ];
    }
}
