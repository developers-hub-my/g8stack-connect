<?php

namespace Database\Factories;

use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataSourceSchema>
 */
class DataSourceSchemaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'data_source_id' => DataSource::factory(),
            'table_name' => fake()->word().'s',
            'columns' => [
                ['name' => 'id', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'name', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'email', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => true],
            ],
            'primary_keys' => ['id'],
            'indexes' => [],
        ];
    }

    public function withColumns(array $columns): static
    {
        return $this->state(fn () => [
            'columns' => collect($columns)->map(fn (string $name) => [
                'name' => $name,
                'type' => 'varchar',
                'nullable' => false,
            ])->all(),
        ]);
    }
}
