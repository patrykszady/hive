<?php

namespace App\Livewire\Estimates;

use App\Models\Bid;
use App\Models\Estimate;
use App\Models\EstimateSection;
use App\Models\Project;

use Livewire\Component;

class EstimateAccept extends Component
{
    public Estimate $estimate;
    public Project $project;

    public $sections = [];
    public $bids = [];
    public $payments = [];
    public $payments_outstanding = 0;
    public $include_reimbursement = FALSE;

    public $modal_show = FALSE;

    protected $listeners = ['accept', 'addPayment'];

    protected function rules()
    {
        return [
            'sections.*.bid_index' => 'nullable',
            'payments.*.description' => 'required|min:3',
            'payments.*.amount' => 'nullable',
            'include_reimbursement' => 'nullable',
        ];
    }

    public function mount(Estimate $estimate)
    {
        $this->project = $estimate->project;
        $this->estimate = $estimate;

        if(!is_null($this->estimate->reimbursments)){
            $this->include_reimbursement = TRUE;
        }

        $this->bids = $this->project->bids()->vendorBids($this->estimate->vendor->id)->with('estimate_sections')->orderBy('type')->get();

        if($this->bids->isEmpty()){
            $bid = Bid::create([
                'amount' => 0.00,
                'type' => 1,
                'project_id' => $this->project->id,
                'vendor_id' => auth()->user()->vendor->id,
            ]);

            $this->bids->push($bid);
        }

        $bids = $this->bids;

        $this->sections =
            $this->estimate
                ->estimate_sections
                ->each(function ($item, $key) use($bids) {
                    if($item->bid){
                        $bid_index = $bids->search(function($bid) use($item) {
                            return $item->bid->id === $bid->id;
                        });
                        $item->bid_index = $bid_index;
                    }else{
                        $item->bid_index = NULL;
                    }
                });
        if($this->estimate->payments){
            $this->payments = collect($this->estimate->payments);
        }else{
            $this->payments = [
                0 => [
                    'amount' => NULL,
                    'description' => NULL
                ]
            ];

            $this->payments = collect($this->payments);
        }
    }

    public function accept()
    {
        $this->modal_show = TRUE;
    }

    //new estiamte Bid
    public function newEstimateBid($section_index)
    {
        $bid_index = count($this->bids);
        $bid = Bid::create([
            'amount' => 0.00,
            'type' => $bid_index + 1,
            'project_id' => $this->project->id,
            'vendor_id' => auth()->user()->vendor->id,
        ]);
        $this->bids->push($bid);
        $this->sections[$section_index]->bid_index = $bid_index;
    }

    public function getPaymentsRemainingProperty()
    {
        $sections_total = $this->sections->where('bid_index', 0)->sum('total');
        $payments_sum = $this->payments->where('amount', '!=', '')->sum('amount');
        $this->payments_outstanding = round($sections_total - $payments_sum, 2);

        return $this->payments_outstanding;
    }

    //new Payment split
    public function addPayment()
    {
        $payment = [
            'amount' => NULL,
            'description' => NULL
        ];

        $this->payments->push($payment);
        $this->payments = $this->payments->values();
    }

    public function removePayment($index)
    {
        $this->payments->forget($index);
        $this->payments = $this->payments->values();
    }


    public function save()
    {
        if($this->payments_outstanding < 0){
            $this->addError('payments_remaining_error', 'Amount Remaining cannot be less than $0.00');
        }else{
            if($this->include_reimbursement){
                $this->estimate->options = ['include_reimbursement' => TRUE];
                $this->estimate->save();
            }else{
                $this->estimate->options = ['include_reimbursement' => FALSE];
                $this->estimate->save();
            }

            if($this->payments->where('amount', '!=', '')->sum('amount') != 0){
                $estimate = $this->estimate;
                $estimate_options = $this->estimate->options;
                $estimate_options['payments'] = $this->payments->toArray();
                $estimate->options = $estimate_options;
                $estimate->save();
            }

            // dd($this->project->finances['reimbursments']);
            foreach($this->bids as $bid_index => $bid){
                $bid_sections = $this->sections->whereNotNull('bid_index')->where('bid_index', $bid_index);

                if($bid_sections->isEmpty() && $bid->amount == 0.00){
                    $bid->delete();
                }elseif(!$bid_sections->isEmpty()){
                    $bid_amount = $bid_sections->sum('total');
                    $bid->amount = $bid_amount;
                    $bid->save();

                    foreach($bid_sections as $section){
                        //ignore 'bid_index' attribute when saving
                        $section->offsetUnset('bid_index');
                        $section->bid_id = $bid->id;
                        $section->save();

                        $section->bid_index = $bid_index;
                    }
                }
            }

            $this->modal_show = FALSE;

            $this->dispatch('refreshComponent')->to('estimates.estimate-show');

            $this->dispatch('notify',
                type: 'success',
                content: 'Estimate Finalized'
            );
        }
    }

    public function render()
    {
        return view('livewire.estimates.accept');
    }
}
