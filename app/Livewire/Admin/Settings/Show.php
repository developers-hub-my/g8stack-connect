<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Settings\ConnectionSettings;
use App\Settings\G8StackSettings;
use App\Settings\GeneralSettings;
use App\Settings\MailSettings;
use App\Settings\NotificationSettings;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    public string $section;

    public array $settings = [];

    public function mount(string $section): void
    {
        $this->authorize('manage.settings');

        $this->section = $section;
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $generalSettings = app(GeneralSettings::class);
        $mailSettings = app(MailSettings::class);
        $notificationSettings = app(NotificationSettings::class);

        $connectionSettings = app(ConnectionSettings::class);
        $g8stackSettings = app(G8StackSettings::class);

        $this->settings = [
            'general' => [
                'site_name' => $generalSettings->site_name,
            ],
            'email' => [
                'from_address' => $mailSettings->from_address,
                'from_name' => $mailSettings->from_name,
            ],
            'notifications' => [
                'enabled' => $notificationSettings->enabled,
                'channels' => $notificationSettings->channels,
            ],
            'connection' => [
                'max_preview_rows' => $connectionSettings->max_preview_rows,
                'connection_timeout' => $connectionSettings->connection_timeout,
                'enforce_readonly' => $connectionSettings->enforce_readonly,
            ],
            'g8stack' => [
                'endpoint' => $g8stackSettings->endpoint,
                'api_token' => $g8stackSettings->api_token,
                'push_enabled' => $g8stackSettings->push_enabled,
            ],
        ];
    }

    public function saveSettings(): void
    {
        $this->authorize('manage.settings');

        try {
            if ($this->section === 'general') {
                $this->validate([
                    'settings.general.site_name' => 'required|string|max:255',
                ]);

                $settings = app(GeneralSettings::class);
                $settings->site_name = $this->settings['general']['site_name'];
                $settings->save();

            } elseif ($this->section === 'email') {
                $this->validate([
                    'settings.email.from_address' => 'required|email',
                    'settings.email.from_name' => 'required|string|max:255',
                ]);

                $settings = app(MailSettings::class);
                $settings->from_address = $this->settings['email']['from_address'];
                $settings->from_name = $this->settings['email']['from_name'];
                $settings->save();

            } elseif ($this->section === 'notifications') {
                $settings = app(NotificationSettings::class);
                $settings->enabled = $this->settings['notifications']['enabled'] ?? false;
                $settings->channels = $this->settings['notifications']['channels'] ?? [];
                $settings->save();

            } elseif ($this->section === 'connection') {
                $this->validate([
                    'settings.connection.max_preview_rows' => 'required|integer|min:1|max:10',
                    'settings.connection.connection_timeout' => 'required|integer|min:5|max:120',
                ]);

                $settings = app(ConnectionSettings::class);
                $settings->max_preview_rows = (int) $this->settings['connection']['max_preview_rows'];
                $settings->connection_timeout = (int) $this->settings['connection']['connection_timeout'];
                $settings->enforce_readonly = $this->settings['connection']['enforce_readonly'] ?? true;
                $settings->save();

            } elseif ($this->section === 'g8stack') {
                $this->validate([
                    'settings.g8stack.endpoint' => 'nullable|url|max:255',
                ]);

                $settings = app(G8StackSettings::class);
                $settings->endpoint = $this->settings['g8stack']['endpoint'] ?? '';
                $settings->api_token = $this->settings['g8stack']['api_token'] ?? '';
                $settings->push_enabled = $this->settings['g8stack']['push_enabled'] ?? false;
                $settings->save();
            }

            $this->dispatch('toast',
                type: 'success',
                message: ucfirst($this->section).' settings saved successfully!',
                duration: 3000
            );

            $this->loadSettings();

        } catch (ValidationException $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Please fix the validation errors.',
                duration: 5000
            );
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Failed to save settings: '.$e->getMessage(),
                duration: 5000
            );
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.settings.show');
    }
}
