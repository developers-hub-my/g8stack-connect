<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Models\DataSource;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public DataSource $dataSource;

    public function mount(string $uuid): void
    {
        $this->dataSource = DataSource::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $this->dataSource);
    }

    public function render(): View
    {
        return view('livewire.data-source.show', [
            'schemas' => $this->dataSource->schemas,
            'specs' => $this->dataSource->specs()->latest()->get(),
        ]);
    }
}
