<?php

namespace App\Livewire\Clients;

use App\Models\Client;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientsShow extends Component
{
    use AuthorizesRequests;

    public Client $client;

    protected $listeners = ['refreshComponent' => '$refresh'];

    #[Title('Client')]
    public function render()
    {
        $this->authorize('view', $this->client);

        return view('livewire.clients.show', [
            'users' => $this->client->users,
        ]);
    }
}
