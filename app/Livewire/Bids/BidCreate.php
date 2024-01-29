<?php

namespace App\Livewire\Bids;

use App\Models\Bid;
use App\Models\Project;
use App\Models\Vendor;

use App\Livewire\Forms\BidForm;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BidCreate extends Component
{
    use AuthorizesRequests;

    public BidForm $form;

    public $project = NULL;
    public $vendor = NULL;

    public $view_text = [
        'card_title' => 'Create Bid',
        'button_text' => 'Save Bids',
        'form_submit' => 'save',
    ];

    public $modal_show = FALSE;

    protected $listeners = ['addBids', 'addChangeOrder', 'removeChangeOrder'];

    public function mount(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function updated($field, $value)
    {
        // $index = substr($field, 10, -7);
        // if($field == 'form.bids.' . $index . '.amount'){
        //     $this->bids[$index]['amount'] = $value;
        //     $this->form->bids[$index]['amount'] = $value;
        // }

        // dd($this->bids);
        // $this->validateOnly($field);
    }

    public function addBids(Project $project)
    {
        $this->project = $project;
        $this->form->bids = $this->project->bids()->vendorBids($this->vendor->id)->with('estimate_sections')->orderBy('type')->get();

        if($this->form->bids->isEmpty()){
            $bid = Bid::create([
                'amount' => 0.00,
                'type' => 1,
                'project_id' => $this->project->id,
                'vendor_id' =>  $this->vendor->id,
            ]);

            $this->form->bids->push($bid);
        }

        $this->modal_show = TRUE;
    }

    public function addChangeOrder()
    {
        $bid_index = count($this->form->bids);

        $bid = Bid::create([
            'amount' => 0.00,
            'type' => $bid_index + 1,
            'project_id' => $this->project->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $bid->amount = NULL;
        $this->form->bids->push($bid);
    }

    public function removeChangeOrder($index)
    {
        $bid = $this->form->bids[$index];
        $bid->delete();
        $this->form->bids->forget($index);
    }

    public function save()
    {
        $this->form->store();

        $this->mount($this->vendor);
        $this->render();

        // $route_name = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
        // //09-01-2023 should be just one component $refresh..
        // //depends on route coming from... either VendorsPayment or ProjectsShow
        // //01-09-2023 why above? can we do this via session() ? why need to refreshComponent?

        // if($route_name == 'vendors.payment'){
        //     $this->dispatch('updateProjectBids', $this->project->id);
        // }elseif($route_name == 'projects.show'){
        //     $this->dispatch('refreshComponent')->to('projects.projects-show');
        // }else{
        //     dd('in else');
        //     abort(404);
        // }

        $this->dispatch('updateProjectBids', $this->project->id)->to('vendors.vendor-payment-create');
        $this->dispatch('refreshComponent')->to('projects.project-show');
        $this->dispatch('notify',
            type: 'success',
            content: 'Bids Updated'
        );

        $this->modal_show = FALSE;
    }

    public function render()
    {
        return view('livewire.bids.form');
    }
}
