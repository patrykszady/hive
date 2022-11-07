<?php

namespace App\Http\Livewire\Banks;

use App\Models\Bank;
use Livewire\Component;
use App\Models\BankAccount;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BankShow extends Component
{
    use AuthorizesRequests;
    public Bank $bank;
    
    public function render()
    {
        $this->authorize('create', Bank::class);

        return view('livewire.banks.show', [
            'bank' => $this->bank,
        ]);
    }
}
