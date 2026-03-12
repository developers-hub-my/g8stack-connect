<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Enums\SpecStatus;
use App\Enums\WizardMode;
use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\ApiSpecTable;
use App\Models\DataSource;
use App\Models\DataSourceSchema;
use App\Models\User;
use App\Services\SpecGenerator\SpecRegenerationService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Shared sample schema definition used across all data source types.
     * Each entry: table_name => columns array.
     */
    protected function sampleSchema(): array
    {
        return [
            'employees' => [
                ['name' => 'id', 'type_name' => 'bigint', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'name', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'email', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => true],
                ['name' => 'ic_number', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => true],
                ['name' => 'department', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => true],
                ['name' => 'salary', 'type_name' => 'decimal', 'type' => 'decimal', 'nullable' => true],
                ['name' => 'hired_at', 'type_name' => 'date', 'type' => 'date', 'nullable' => true],
                ['name' => 'created_at', 'type_name' => 'timestamp', 'type' => 'timestamp', 'nullable' => true],
            ],
            'departments' => [
                ['name' => 'id', 'type_name' => 'bigint', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'name', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'code', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'budget', 'type_name' => 'decimal', 'type' => 'decimal', 'nullable' => true],
                ['name' => 'created_at', 'type_name' => 'timestamp', 'type' => 'timestamp', 'nullable' => true],
            ],
            'projects' => [
                ['name' => 'id', 'type_name' => 'bigint', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'name', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => false],
                ['name' => 'department_code', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => true],
                ['name' => 'status', 'type_name' => 'varchar', 'type' => 'varchar', 'nullable' => true],
                ['name' => 'start_date', 'type_name' => 'date', 'type' => 'date', 'nullable' => true],
                ['name' => 'end_date', 'type_name' => 'date', 'type' => 'date', 'nullable' => true],
                ['name' => 'created_at', 'type_name' => 'timestamp', 'type' => 'timestamp', 'nullable' => true],
            ],
        ];
    }

    public function run(): void
    {
        $user = User::where('email', config('seeder.users.superadmin.email'))->first();

        if (! $user) {
            $this->command->warn('Superadmin user not found. Run PrepareSeeder first.');

            return;
        }

        $this->seedDatabaseSources($user);
        $this->seedFileSources($user);

        $this->command->info('Demo data seeded for all data source types.');
    }

    protected function seedDatabaseSources(User $user): void
    {
        $databases = [
            [
                'name' => 'Demo MySQL',
                'type' => DataSourceType::MYSQL,
                'credentials' => [
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'database' => 'g8test',
                    'username' => 'g8test',
                    'password' => 'g8testpass',
                ],
                'wizard_mode' => WizardMode::GUIDED,
                'tables' => ['employees', 'departments', 'projects'],
            ],
            [
                'name' => 'Demo PostgreSQL',
                'type' => DataSourceType::POSTGRESQL,
                'credentials' => [
                    'host' => '127.0.0.1',
                    'port' => 5432,
                    'database' => 'g8test',
                    'username' => 'g8test',
                    'password' => 'g8testpass',
                ],
                'wizard_mode' => WizardMode::GUIDED,
                'tables' => ['employees', 'departments', 'projects'],
            ],
            [
                'name' => 'Demo MSSQL',
                'type' => DataSourceType::MSSQL,
                'credentials' => [
                    'host' => '127.0.0.1',
                    'port' => 1433,
                    'database' => 'g8test',
                    'username' => 'sa',
                    'password' => 'G8test@Pass1',
                ],
                'wizard_mode' => WizardMode::GUIDED,
                'tables' => ['employees', 'departments', 'projects'],
            ],
            [
                'name' => 'Demo SQLite',
                'type' => DataSourceType::SQLITE,
                'credentials' => [
                    'database' => storage_path('app/demo.sqlite'),
                ],
                'wizard_mode' => WizardMode::SIMPLE,
                'tables' => ['employees'],
            ],
        ];

        foreach ($databases as $db) {
            $this->createDataSourceWithSpec($user, $db);
        }
    }

    protected function seedFileSources(User $user): void
    {
        $fileSources = [
            [
                'name' => 'Demo CSV',
                'type' => DataSourceType::CSV,
                'credentials' => [
                    'file_path' => storage_path('app/demo-data/employees.csv'),
                    'original_filename' => 'employees.csv',
                ],
                'wizard_mode' => WizardMode::SIMPLE,
                'tables' => ['employees'],
                'read_only' => true,
            ],
            [
                'name' => 'Demo JSON',
                'type' => DataSourceType::JSON,
                'credentials' => [
                    'file_path' => storage_path('app/demo-data/employees.json'),
                    'original_filename' => 'employees.json',
                ],
                'wizard_mode' => WizardMode::SIMPLE,
                'tables' => ['employees'],
                'read_only' => true,
            ],
            [
                'name' => 'Demo Excel',
                'type' => DataSourceType::EXCEL,
                'credentials' => [
                    'file_path' => storage_path('app/demo-data/employees.xlsx'),
                    'original_filename' => 'employees.xlsx',
                ],
                'wizard_mode' => WizardMode::SIMPLE,
                'tables' => ['employees'],
                'read_only' => true,
            ],
        ];

        $this->createDemoFiles();

        foreach ($fileSources as $file) {
            $this->createDataSourceWithSpec($user, $file);
        }
    }

    protected function createDataSourceWithSpec(User $user, array $config): void
    {
        $dataSource = DataSource::create([
            'name' => $config['name'],
            'type' => $config['type']->value,
            'credentials' => $config['credentials'],
            'status' => ConnectionStatus::INTROSPECTED,
            'user_id' => $user->id,
            'metadata' => [],
        ]);

        $schema = $this->sampleSchema();

        // Create DataSourceSchema for each table
        foreach ($config['tables'] as $tableName) {
            DataSourceSchema::create([
                'data_source_id' => $dataSource->id,
                'table_name' => $tableName,
                'columns' => $schema[$tableName] ?? $schema['employees'],
                'primary_keys' => ['id'],
                'indexes' => [],
            ]);
        }

        $isReadOnly = $config['read_only'] ?? false;
        $wizardMode = $config['wizard_mode'];

        $apiSpec = ApiSpec::create([
            'user_id' => $user->id,
            'data_source_id' => $dataSource->id,
            'name' => $config['name'].' API',
            'wizard_mode' => $wizardMode->value,
            'status' => SpecStatus::DEPLOYED->value,
            'openapi_spec' => [],
            'selected_tables' => $config['tables'],
            'configuration' => [
                'mode' => $wizardMode->value,
                'pii_excluded' => ['ic_number'],
                'read_only' => $isReadOnly,
            ],
        ]);

        // Create ApiSpecTable + ApiSpecField for each table
        foreach ($config['tables'] as $index => $tableName) {
            $operations = [
                'list' => true,
                'show' => true,
                'create' => ! $isReadOnly,
                'update' => ! $isReadOnly,
                'delete' => false,
            ];

            $specTable = ApiSpecTable::create([
                'api_spec_id' => $apiSpec->id,
                'table_name' => $tableName,
                'resource_name' => $tableName,
                'operations' => $operations,
                'sort_order' => $index,
            ]);

            $columns = $schema[$tableName] ?? $schema['employees'];

            foreach ($columns as $colIndex => $col) {
                $isPii = in_array($col['name'], ['ic_number', 'password', 'secret']);

                ApiSpecField::create([
                    'api_spec_id' => $apiSpec->id,
                    'api_spec_table_id' => $specTable->id,
                    'column_name' => $col['name'],
                    'display_name' => $col['name'],
                    'data_type' => $col['type_name'],
                    'is_exposed' => ! $isPii,
                    'is_pii' => $isPii,
                    'is_required' => ! $col['nullable'],
                    'is_filterable' => in_array($col['name'], ['name', 'department', 'code', 'status']),
                    'is_sortable' => in_array($col['name'], ['name', 'salary', 'hired_at', 'created_at', 'budget', 'start_date']),
                    'sort_order' => $colIndex,
                ]);
            }
        }

        // Generate OpenAPI spec using the regeneration service
        $spec = app(SpecRegenerationService::class)->regenerate($apiSpec);

        if (! empty($spec)) {
            $this->command->info("  ✓ {$config['name']} — {$apiSpec->slug}");
        } else {
            $this->command->warn("  ⚠ {$config['name']} — spec generation returned empty");
        }
    }

    /**
     * Create demo flat files for CSV, JSON, and Excel sources.
     */
    protected function createDemoFiles(): void
    {
        $dir = storage_path('app/demo-data');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // CSV
        $csv = "id,name,email,ic_number,department,salary,hired_at,created_at\n";
        $csv .= "1,Ali Ahmad,ali@example.com,880101-01-1234,Engineering,8500.00,2020-03-15,2024-01-01 00:00:00\n";
        $csv .= "2,Siti Aminah,siti@example.com,900202-02-5678,Marketing,7200.00,2021-06-01,2024-01-01 00:00:00\n";
        $csv .= "3,Kumar Raj,kumar@example.com,850303-03-9012,Finance,9100.00,2019-11-20,2024-01-01 00:00:00\n";
        $csv .= "4,Mei Ling,meiling@example.com,920404-04-3456,Engineering,8800.00,2022-01-10,2024-01-01 00:00:00\n";
        $csv .= "5,Ahmad Razak,razak@example.com,870505-05-7890,Operations,7500.00,2023-04-05,2024-01-01 00:00:00\n";
        file_put_contents("{$dir}/employees.csv", $csv);

        // JSON
        $json = [
            ['id' => 1, 'name' => 'Ali Ahmad', 'email' => 'ali@example.com', 'ic_number' => '880101-01-1234', 'department' => 'Engineering', 'salary' => 8500.00, 'hired_at' => '2020-03-15', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 2, 'name' => 'Siti Aminah', 'email' => 'siti@example.com', 'ic_number' => '900202-02-5678', 'department' => 'Marketing', 'salary' => 7200.00, 'hired_at' => '2021-06-01', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 3, 'name' => 'Kumar Raj', 'email' => 'kumar@example.com', 'ic_number' => '850303-03-9012', 'department' => 'Finance', 'salary' => 9100.00, 'hired_at' => '2019-11-20', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 4, 'name' => 'Mei Ling', 'email' => 'meiling@example.com', 'ic_number' => '920404-04-3456', 'department' => 'Engineering', 'salary' => 8800.00, 'hired_at' => '2022-01-10', 'created_at' => '2024-01-01 00:00:00'],
            ['id' => 5, 'name' => 'Ahmad Razak', 'email' => 'razak@example.com', 'ic_number' => '870505-05-7890', 'department' => 'Operations', 'salary' => 7500.00, 'hired_at' => '2023-04-05', 'created_at' => '2024-01-01 00:00:00'],
        ];
        file_put_contents("{$dir}/employees.json", json_encode($json, JSON_PRETTY_PRINT));

        // Excel — create a simple XLSX using PhpSpreadsheet
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('employees');

            $headers = ['id', 'name', 'email', 'ic_number', 'department', 'salary', 'hired_at', 'created_at'];
            foreach ($headers as $col => $header) {
                $sheet->setCellValue([$col + 1, 1], $header);
            }

            $rows = [
                [1, 'Ali Ahmad', 'ali@example.com', '880101-01-1234', 'Engineering', 8500.00, '2020-03-15', '2024-01-01 00:00:00'],
                [2, 'Siti Aminah', 'siti@example.com', '900202-02-5678', 'Marketing', 7200.00, '2021-06-01', '2024-01-01 00:00:00'],
                [3, 'Kumar Raj', 'kumar@example.com', '850303-03-9012', 'Finance', 9100.00, '2019-11-20', '2024-01-01 00:00:00'],
                [4, 'Mei Ling', 'meiling@example.com', '920404-04-3456', 'Engineering', 8800.00, '2022-01-10', '2024-01-01 00:00:00'],
                [5, 'Ahmad Razak', 'razak@example.com', '870505-05-7890', 'Operations', 7500.00, '2023-04-05', '2024-01-01 00:00:00'],
            ];

            foreach ($rows as $rowIndex => $row) {
                foreach ($row as $col => $value) {
                    $sheet->setCellValue([$col + 1, $rowIndex + 2], $value);
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save("{$dir}/employees.xlsx");
        } else {
            $this->command->warn('  ⚠ PhpSpreadsheet not installed — skipping Excel demo file.');
        }

        $this->command->info('  ✓ Demo files created in storage/app/demo-data/');
    }
}
