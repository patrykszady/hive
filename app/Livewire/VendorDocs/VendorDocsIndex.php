<?php

namespace App\Livewire\VendorDocs;

use App\Models\VendorDoc;
use App\Models\Check;
use App\Models\Vendor;

use Livewire\Attributes\Title;
use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorDocsIndex extends Component
{
    use AuthorizesRequests;

    public $view = NULL;
    public $date = [];

    protected $listeners = ['refreshComponent' => '$refresh'];

    #[Title('Certificates')]
    public function render()
    {
        $this->authorize('viewAny', VendorDoc::class);
        // $this->date['start'] = today()->subYear(1)->format('Y-m-d');
        // $this->date['end'] = today()->format('Y-m-d');

        // $checks = Check::whereBetween('date', [$this->date['start'], $this->date['end']])->whereNull('user_id')->get()->groupBy('vendor_id');

        // dd($checks);
        //dont show where havent done business with / no checks in the last YTD

        //get latest for each type only
        //['vendor_id', 'type']
        //->orderBy('type', 'DESC')
        // $docs = VendorDoc::with('vendor')->orderBy('expiration_date', 'DESC')->get()->groupBy('vendor_id');
        // dd($docs);

        $vendors = Vendor::has('vendor_docs')->with('vendor_docs')->get();
        foreach($vendors as $vendor){
            $doc_types = $vendor->vendor_docs()->orderBy('expiration_date', 'DESC')->with('agent')->get()->groupBy('type');

            foreach($doc_types as $type_certificates)
            {
                if($type_certificates->first()->expiration_date <= today()){
                    $vendor->expired_docs = TRUE;
                }
            }
        }
        //where vendor has a check in the last year ...
        return view('livewire.vendor-docs.index', [
            'vendors' => $vendors,
        ]);
    }
}
