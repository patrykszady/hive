<?php

namespace App\Livewire\VendorDocs;

use App\Models\Agent;
use App\Models\Vendor;
use App\Models\VendorDoc;

use Livewire\WithFileUploads;
use Livewire\Component;

use App\Mail\RequestInsurance;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;

class VendorDocCreate extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public Vendor $vendor;
    // public VendorDoc $vendor_doc;
    public $doc_file = NULL;
    public $modal_show = FALSE;

    protected $listeners = ['addDocument', 'requestDocument', 'downloadDocuments'];

    protected function rules()
    {
        return [
            'doc_file' => 'required|mimes:pdf',
            // 'type' => 'required',
        ];
    }

    public function addDocument(Vendor $vendor)
    {
        $this->vendor = $vendor;
        $this->modal_show = TRUE;
    }

    public function downloadDocuments($doc_filenames)
    {
        dd('in downloadDocuments');
        $this->vendor = $vendor;
        $this->modal_show = TRUE;
    }

    public function requestDocument(Vendor $vendor)
    {
        //send email...
        $doc_types = $vendor->vendor_docs()->orderBy('expiration_date', 'DESC')->with('agent')->get()->groupBy('type');

        $latest_docs = collect();
        foreach($doc_types as $type_certificates)
        {
            if($type_certificates->first()->expiration_date <= today()){
                $latest_docs->push($type_certificates->first());
            }
        }

        $agent_ids = $latest_docs->groupBy('agent_id');

        foreach($agent_ids as $agent_id => $agent_expired_docs)
        {
            $agent = Agent::find($agent_id);

            //if no agent, send to Vendor only
            if(!is_null($agent)){
                $agent_email = $agent->email;
            }else{
                $agent_email = $vendor->business_email;
            }

            //send email to agent, vendor, and auth()->vendor() with all $agent_expired_docs
            Mail::to($agent_email)
                ->cc([$vendor->business_email, auth()->user()->vendor->business_email])
                ->send(new RequestInsurance($agent_expired_docs, $vendor));

            //notification
            $this->dispatch('notify',
                type: 'success',
                content: 'Vendor Document Requested',
                // route: 'expenses/' . $expense->id
            );
        }
    }

    public function store()
    {
        // $this->validate();
        // $this->authorize('update', $this->expense);

        //save file for this->vendor
        $ocr_filename = $this->vendor->id . '-' . auth()->user()->vendor->id . '-' . date('Y-m-d-H-i-s') . '.' . $this->doc_file->getClientOriginalExtension();
        $ocr_path = 'files/vendor_docs/' . $ocr_filename;
        $this->doc_file->storeAs('vendor_docs', $ocr_filename, 'files');

        //send for form recornizer ocr with file uri
        $insurance_info = app('App\Http\Controllers\VendorDocsController')->find_insurance_data($ocr_path);

        // dd($insurance_info);
        //save/update Agent from the certificate
        if(isset($insurance_info['agent_email']['valueString'])){
            $agent = Agent::where('email', $insurance_info['agent_email']['valueString'])->first();

            if(is_null($agent)){
                $agent = Agent::create([
                    'name' => isset($insurance_info['agent_name']['valueString']) ? $insurance_info['agent_name']['valueString'] : NULL,
                    'business_name' => isset($insurance_info['agent_agency']['valueString']) ? $insurance_info['agent_agency']['valueString'] : NULL,
                    'address' => isset($insurance_info['agent_agency_address']['valueString']) ? $insurance_info['agent_agency_address']['valueString'] : NULL,
                    'phone' => isset($insurance_info['agent_phone']['content']) ? $insurance_info['agent_phone']['content'] : NULL,
                    'email' => isset($insurance_info['agent_email']['valueString']) ? $insurance_info['agent_email']['valueString'] : NULL,
                ]);
            }
        }

        //error ... already exists
        //create vendor_doc for each $insurance_info
        $vendor_docs = [];
        if(isset($insurance_info['general_policy_number']['valueString'])){
            //check if exists
            $vendor_doc = VendorDoc::where('number', $insurance_info['general_policy_number']['valueString'])
                ->where('expiration_date', $insurance_info['general_exp']['valueDate'])->first();

            if(is_null($vendor_doc)){
                $vendor_docs[] = 'general';
            }
        }

        if(isset($insurance_info['workers_policy_number']['valueString'])){
            if(str_replace(' ', '', $insurance_info['workers_policy_number']['valueString']) != 'N/A'){
                //check if exists
                $vendor_doc = VendorDoc::where('number', $insurance_info['workers_policy_number']['valueString'])
                    ->where('expiration_date', $insurance_info['workers_exp']['valueDate'])->first();

                if(is_null($vendor_doc)){
                    $vendor_docs[] = 'workers';
                }
            }
        }

        foreach($vendor_docs as $vendor_doc){
            $vendor_doc = VendorDoc::create([
                'type' => $vendor_doc,
                'vendor_id' => $this->vendor->id,
                'effective_date' => $insurance_info[$vendor_doc . '_eff']['valueDate'],
                'expiration_date' => $insurance_info[$vendor_doc . '_exp']['valueDate'],
                'number' => $insurance_info[$vendor_doc . '_policy_number']['valueString'],
                'belongs_to_vendor_id' => auth()->user()->vendor->id,
                'doc_filename' => $ocr_filename
            ]);

            //link agent and insurance
            if(isset($agent)){
                $vendor_doc->agent_id = $agent->id;
                $vendor_doc->save();
            }
        }

        $this->modal_show = FALSE;
        $this->doc_file = NULL;

        $this->dispatch('refreshComponent')->to('vendors.vendor-show');
        $this->dispatch('refreshComponent')->to('vendor-docs.vendor-docs-index');

        $this->dispatch('notify',
            type: 'success',
            content: 'Vendor Document Added',
            // route: 'expenses/' . $expense->id
        );
    }

    public function render()
    {
        return view('livewire.vendor-docs.form');
    }
}
