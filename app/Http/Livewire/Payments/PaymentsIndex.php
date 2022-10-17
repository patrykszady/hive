<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;

class PaymentsIndex extends Component
{
    //sort by project, client
    public function render()
    {
        return view('livewire.payments.index');
    }
}