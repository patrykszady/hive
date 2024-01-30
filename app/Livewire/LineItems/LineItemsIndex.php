<?php

namespace App\Livewire\LineItems;

use App\Models\LineItem;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LineItemsIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    // public $view;
    public $search = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    #[Title('Line Items')]
    public function render()
    {
        $this->authorize('viewAny', LineItem::class);

        return view('livewire.line-items.index', [
            'line_items' => LineItem::orderBy('created_at', 'DESC')->where('name', 'like', '%' . $this->search . '%')->orWhere('desc', 'like', '%' . $this->search . '%')->get(),
        ]);
    }
}
