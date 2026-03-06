<?php

declare(strict_types=1);

namespace App\Livewire\ApiSpec;

use App\Models\ApiSpec;
use Livewire\Component;

class Show extends Component
{
    public ApiSpec $apiSpec;

    public function mount(string $uuid): void
    {
        $this->apiSpec = ApiSpec::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $this->apiSpec);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.api-spec.show', [
            'versions' => $this->apiSpec->versions,
        ]);
    }
}
