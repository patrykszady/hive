<?php

namespace App\Http\Livewire\Payments;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;

use Livewire\Component;

class PaymentsForm extends Component
{
    public Client $client;

    public $project = NULL;
    public $project_id = NULL;
    public $payment_projects = [];
    public $payment = NULL;

    protected $listeners = ['addProject', 'removeProject'];

    protected function rules()
    {
        return [    
            'payment_projects.*.amount' => 'required|numeric|min:0.01|regex:/^-?\d+(\.\d{1,2})?$/',
            'project_id' => 'nullable',
            'payment.date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'payment.invoice' => 'nullable',
            'payment.note' => 'nullable',
        ];
    }

    public function mount()
    {
        $this->payment_projects = collect();

        $this->payment = Payment::make();
        $this->payment->date = today()->format('Y-m-d');

        $this->view_text = [
            'card_title' => 'Create Client Payment',
            'button_text' => 'Add Payment for Projects',
            'form_submit' => 'store',             
        ];
    }

    public function updatedProjectId()
    {
        $this->project = Project::findOrFail($this->project_id);
    }

    public function getClientPaymentSumProperty()
    {
        return collect($this->payment_projects)->where('amount', '!=', '')->sum('amount');
    }

    // 8-31-2022 same on VendorPaymentForm
    public function addProject()
    {
        // dd('$this->project_id');
        //if $project is already in $payment_projects collection (IGNORE / CONTINUE)
        if(in_array($this->project_id, $this->payment_projects->pluck('id')->toArray())){
            //Does Not reset $this->payment_projects.*.AMOUNT
            $this->addError('project_id', 'Project already in Client Payment. Cannot add.');
        }else{
            $project_details = $this->project;
    
            //add this Model to $payment_projects collection
            $this->payment_projects->put($project_details->id, $project_details);        
            $this->project_id = NULL;
        }
    }

    public function removeProject($project_id_to_remove)
    {
        //project_id = key in $this->payment_projects
        //remove this Model from $payment_projects collection
        $this->payment_projects->forget($project_id_to_remove);
    }

    public function store()
    {
        $this->validate();
        //validate payment total is greater than $0

        foreach($this->payment_projects as $payment_project){
            $payment = Payment::create([
                'amount' => $payment_project['amount'],
                'project_id' => $payment_project['id'],
                'date' => $this->payment['date'],
                'reference' => $this->payment['invoice'],
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'note' => $this->payment['note'],
                'created_by_user_id' => auth()->user()->id,                
            ]);
        }
        dd('in store past validation');
    }

    public function render()
    {
        //client projects ONLY
        //8-31-2022 wherre project belongs to auth()->user()->vendor
        $projects = Project::where('client_id', $this->client->id)->orderBy('created_at', 'DESC')->get();

        return view('livewire.payments.form', [
            'projects' => $projects,
        ]);
    }
}
