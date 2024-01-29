<?php

namespace App\Livewire\Payments;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;

use Livewire\Component;
use Livewire\Attributes\Title;

use App\Livewire\Forms\PaymentForm;

use Carbon\Carbon;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentCreate extends Component
{
    use AuthorizesRequests;

    public PaymentForm $form;

    public Client $client;

    public $projects = [];
    // public $project = NULL;
    // public $project_id = NULL;
    // public $payment_projects = [];
    // public $payment = NULL;
    // public $parent_payment = NULL;

    protected $listeners = ['addProject', 'removeProject'];

    protected function rules()
    {
        return [
            'projects.*.show' => 'nullable',
            'projects.*.amount' => 'required|numeric|min:0.01|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    public function mount()
    {
        $this->authorize('create', Payment::class);

        $this->projects =
            Project::where('created_at', '>', Carbon::now()->subYears(2)->format('Y-m-d'))
                ->where('client_id', $this->client->id)
                ->orderBy('created_at', 'DESC')
                ->whereHas('project_status', function ($query) {
                    $query->whereIn('project_status.title', ['Active', 'Complete']);
                })
                // ->with(['expenses' => function ($query) {
                //     return $query->where('vendor_id', '4');
                //     }])
                ->get()
                ->each(function ($item, $key) {
                    $item->show = false;
                    // $item->show_timestamp = now();
                })
                ->keyBy('id');

        $this->form->date = today()->format('Y-m-d');
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function updatedProjectId()
    {
        $this->project = Project::findOrFail($this->project_id);
    }

    public function getClientPaymentSumProperty()
    {
        return collect($this->projects)->where('show', true)->where('amount', '>' , 0)->sum('amount');
    }

    // 8-31-2022 | 9-10-2023 same on VendorPaymentForm
    public function addProject()
    {
        $project = $this->projects->where('id', $this->form->project_id)->first();
        $project->show = true;

        $this->form->project_id = "";
    }

    public function removeProject($project_id_to_remove)
    {
        $project = $this->projects->where('id', $project_id_to_remove)->first();
        $project->show = false;
        $project->amount = 0;

        $this->form->project_id = "";
    }

    public function save()
    {
        //validate payment total is greater than $0
        //if less than or equal to 0... send back with error
        if($this->getClientPaymentSumProperty() <= 0){
            return $this->addError('payment_total_min', 'Payment total needs to be greater than $0 and include at least 1 project.');
        }else{
            $payment = $this->form->store();
        }

        return redirect()->route('projects.show', $payment->project_id);
    }

    #[Title('Payment')]
    public function render()
    {
        //client projects ONLY
        //8-31-2022 wherre project belongs to auth()->user()->vendor
        $projects = $this->projects;

        $view_text = [
            'card_title' => 'Create Client Payment',
            'button_text' => 'Add Payment for Projects',
            'form_submit' => 'save',
        ];

        return view('livewire.payments.form', [
            'projects' => $projects,
            'view_text' => $view_text,
        ]);
    }
}
