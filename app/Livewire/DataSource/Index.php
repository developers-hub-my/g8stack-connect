<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Models\DataSource;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('viewAny', DataSource::class);
    }

    public function render(): View
    {
        $query = DataSource::query()
            ->where('user_id', auth()->id())
            ->latest();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.data-source.index', [
            'dataSources' => $query->paginate(10),
        ]);
    }
}
