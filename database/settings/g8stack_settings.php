<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('g8stack.endpoint', '');
        $this->migrator->add('g8stack.api_token', '');
        $this->migrator->add('g8stack.push_enabled', false);
    }
};
