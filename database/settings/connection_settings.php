<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('connection.max_preview_rows', 5);
        $this->migrator->add('connection.connection_timeout', 30);
        $this->migrator->add('connection.enforce_readonly', true);
    }
};
