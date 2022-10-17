<?php

namespace App\Http\Livewire\Banks;

use App\Models\Bank;
use Livewire\Component;
use App\Models\BankAccount;

class BankShow extends Component
{
    public Bank $bank;
    
    public function render()
    {
        return view('livewire.banks.show', [
            'bank' => $this->bank,
        ]);
    }
}
