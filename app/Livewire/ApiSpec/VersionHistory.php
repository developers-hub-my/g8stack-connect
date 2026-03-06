<?php

declare(strict_types=1);

namespace App\Livewire\ApiSpec;

use App\Models\ApiSpec;
use Livewire\Component;

class VersionHistory extends Component
{
    public ApiSpec $apiSpec;

    public ?int $selectedVersion = null;

    public function mount(string $specUuid): void
    {
        $this->apiSpec = ApiSpec::where('uuid', $specUuid)->firstOrFail();
        $this->authorize('view', $this->apiSpec);
    }

    public function selectVersion(int $versionNumber): void
    {
        $this->selectedVersion = $versionNumber;
    }

    public function render(): \Illuminate\View\View
    {
        $versions = $this->apiSpec->versions;
        $currentVersion = $this->selectedVersion
            ? $versions->firstWhere('version_number', $this->selectedVersion)
            : $versions->first();

        return view('livewire.api-spec.version-history', [
            'versions' => $versions,
            'currentVersion' => $currentVersion,
        ]);
    }
}
