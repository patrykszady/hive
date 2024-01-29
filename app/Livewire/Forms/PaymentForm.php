<?php

namespace App\Livewire\Forms;

use App\Models\Payment;

use Livewire\Attributes\Rule;
use Livewire\Form;

class PaymentForm extends Form
{
    #[Rule('nullable')]
    public $project_id = '';

    #[Rule('required|date|before_or_equal:today|after:2017-01-01')]
    public $date = NULL;

    #[Rule('required')]
    public $invoice = NULL;

    #[Rule('nullable')]
    public $note = NULL;

    public function store()
    {
        $this->validate();

        $parent_payment_id = NULL;
        foreach($this->component->projects->where('show', 'true')->where('amount', '>' , 0) as $key => $project){
            //ignore 'show' attribute when saving
            $project->offsetUnset('show');

            if($key == 0){
                $parent_payment_id = NULL;
            }else{
                $parent_payment_id = $parent_payment_id;
            }

            $payment = Payment::create([
                'amount' => $project->amount,
                'project_id' => $project->id,
                'date' => $this->date,
                'reference' => $this->invoice,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'note' => $this->note,
                'created_by_user_id' => auth()->user()->id,
                'parent_client_payment_id' => $parent_payment_id,
            ]);

            $parent_payment_id = $payment->id;
        }

        return $payment;
    }
}
