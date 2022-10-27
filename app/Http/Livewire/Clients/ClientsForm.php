<?php

namespace App\Http\Livewire\Clients;

use App\Models\Client;

use Livewire\Component;

class ClientsForm extends Component
{

    public function mount()
    {  
        if(isset($this->client)){            
            $this->view_text = [
                'card_title' => 'Update Client',
                'button_text' => 'Update',
                'form_submit' => 'update',             
            ];
        }else{
            $this->view_text = [
                'card_title' => 'Create Client',
                'button_text' => 'Create',
                'form_submit' => 'store',             
            ];
        }
    }
    
    public function render()
    {
        return view('livewire.clients.form');
    }
}
