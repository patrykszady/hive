<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Client;

class ClientsIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $client_name_search = '';
    public $view;

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected $queryString = [
        'client_name_search' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    #[Title('Clients')]
    public function render()
    {
        $clients = Client::orderBy('created_at', 'DESC')
            // ->where('business_name', 'like', "%{$this->business_name}%")
            // ->where('business_type', 'like', "%{$this->vendor_type}%")
            ->when($this->client_name_search, function($query) {
                return $query->whereHas('users', function ($query) {
                    return $query->where('last_name', 'like', "%{$this->client_name_search}%")
                        ->orWhere('first_name', 'like', "%{$this->client_name_search}%");
                  });
            })
            ->orWhere('address', 'like', "%{$this->client_name_search}%")
            ->orWhere('business_name', 'like', "%{$this->client_name_search}%")
            ->paginate(10);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ]);
    }
}
