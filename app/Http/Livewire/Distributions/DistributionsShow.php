<?php

namespace App\Http\Livewire\Distributions;

use App\Models\Distribution;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DistributionsShow extends Component
{
    use AuthorizesRequests;

    public Distribution $distribution;

    public function render()
    {
        dd($this->distribution->expenses->sum('amount') + $this->distribution->splits->sum('amount'));
        return view('livewire.distributions.show');
    }
}
