<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Client;

class ClientsIndex extends Component
{
    use WithPagination, AuthorizesRequests;
    
    public $client_name = '';

    protected $queryString = [
        'client_name' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::orderBy('created_at', 'DESC')
            // ->where('business_name', 'like', "%{$this->business_name}%")
            // ->where('business_type', 'like', "%{$this->vendor_type}%")
            ->when($this->client_name, function($query) {
                return $query->whereHas('users', function ($query) {
                    return $query->where('last_name', 'like', "%{$this->client_name}%")
                        ->orWhere('first_name', 'like', "%{$this->client_name}%");
                  });
            })
            ->orWhere('address', 'like', "%{$this->client_name}%")
            ->orWhere('business_name', 'like', "%{$this->client_name}%")
            ->paginate(10);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ]);
    }
}
