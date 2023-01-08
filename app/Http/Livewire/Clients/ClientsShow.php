<?php

namespace App\Http\Livewire\Clients;

use App\Models\Project;
use App\Models\Client;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientsShow extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public function mount()
    {
        $this->users = $this->client->users;
    }

    public function render()
    {
        $this->authorize('view', $this->client);

        return view('livewire.clients.show', [
        ]);
    }
}