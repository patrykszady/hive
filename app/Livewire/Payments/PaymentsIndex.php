<?php

namespace App\Livewire\Payments;

use Livewire\Component;

class PaymentsIndex extends Component
{
    //sort by project, client
    public function render()
    {
        return view('livewire.payments.index');
    }
}