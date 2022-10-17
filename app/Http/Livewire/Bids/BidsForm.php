<?php

namespace App\Http\Livewire\Bids;

use App\Models\Bid;
use App\Models\Project;
use App\Models\Vendor;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BidsForm extends Component
{
    use AuthorizesRequests;
    
    public $bids = [];
    public $project = NULL;
    public $vendor = NULL;
    public $modal_show = FALSE;

    protected $listeners = ['addBids', 'addChangeOrder', 'removeChangeOrder'];

    protected function rules()
    {
        return [
            'bids.*.amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    public function mount()
    {      
        $this->view_text = [
            'card_title' => 'Create Bid',
            'button_text' => 'Save Bids',
            'form_submit' => 'store',             
        ];
    }

    public function updated($field) 
    {
        // $this->validate();
        $this->validateOnly($field);
    }

    public function addBids(Project $project, Vendor $vendor)
    {    
        $this->project = $project;
        $this->vendor = $vendor;

        //->withoutGlobalScopes()
        $this->bids = $this->project->bids()->where('vendor_id', $vendor->id)->orderBy('type')->get()->toArray();

        if(!$this->bids){
            $this->bids = [];
            array_push($this->bids);
        }

        $this->modal_show = TRUE;
    }

    public function addChangeOrder()
    {
        if(!is_array($this->bids)){
            $this->bids = $this->bids->toArray();
        }
        
        array_push($this->bids, 1);

        //push to collection...
        // $new_bid = Bid::make(['type' => 2]);
        // $this->bids->push($new_bid);
    }

    public function removeChangeOrder($index)
    {
        unset($this->bids[$index]);
    }

    public function store()
    {
        $this->validate();
        // $this->authorize('update', $this->expense);

        $route_name = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();

        $bids_database = $this->project->bids()->where('vendor_id', $this->vendor->id)->get();
        //removed by enduser in the form
        foreach($bids_database as $bid){
            if(!in_array($bid->id, collect($this->bids)->pluck('id')->toArray())){
                $bid->delete();
            }
        }

        foreach($this->bids as $bid){
            if(isset($bid['id'])){
                $bid_database = Bid::findOrFail($bid['id']);
                $bid_database->update([
                    'amount' => $bid['amount'],
                    'project_id' => $this->project->id,
                ]);
            }else{
                //because $this->project->bids keeps a cache and every time foreach runs it uses that data instead of refreshing the just added BID
                $project = Project::findOrFail($this->project->id);

                //if project has NO Bids... bid type = 1, if more: bid type = 2
                if($project->bids()->where('vendor_id', $this->vendor->id)->get()->isEmpty()){
                    $bid_type = 1;
                }else{
                    $bid_type = 2;
                }

                Bid::create([
                    'amount' => $bid['amount'],
                    'type' => $bid_type,
                    'project_id' => $this->project->id,
                    //vendor_id = logged in vendor in Projects Show or Vendor in Vendor Payment
                    'vendor_id' => $this->vendor->id,
                ]);
            }
        }

        //depends on route coming from... either VendorsPayment or ProjectsShow
        $this->modal_show = FALSE;  
        if($route_name == 'vendors.payment'){
            $this->emit('updateProjectBids', $this->project->id);
        }elseif($route_name == 'projects.show'){
            // return back();
            return redirect()->route('projects.show', $this->project);
        }else{
            //throw error 404
            abort(404);
        }

        // $this->project->refresh();
        // $this->project->bids()->refresh();
        $this->modal_show = FALSE;        
    }

    public function render()
    {
        return view('livewire.bids.form');
    }
}