<?php

namespace App\Livewire\Forms;

use App\Models\Bid;

use Livewire\Form;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BidForm extends Form
{
    use AuthorizesRequests;

    public $bids = [];

    public function rules()
    {
        return [
            'bids.*.amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    // public function setBids($bids)
    // {
    //     $this->bids = $bids;
    // }

    public function store()
    {
        // $this->authorize('create', Bid::class); ???
        $this->authorize('create', Expense::class);
        // $this->validate();

        // dd($this->bids);

        foreach($this->bids as $index => $bid){
            $bid->update([
                'amount' => $bid['amount'],
                'project_id' => $this->component->project->id,
            ]);
        }
    }
}
