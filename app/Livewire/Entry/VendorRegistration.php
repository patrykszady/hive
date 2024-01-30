<?php

namespace App\Livewire\Entry;

use App\Models\Vendor;
use App\Models\Project;
use App\Models\Distribution;
use App\Models\Client;
use App\Models\ProjectStatus;
use App\Models\Check;
use App\Models\Payment;
use App\Models\Bid;
use App\Models\Scopes\VendorScope;
use Livewire\Component;

class VendorRegistration extends Component
{
    public Vendor $vendor;
    public $user;

    public $vendor_users;
    public $vendor_add_type;
    public $registration;
    public $icons = [];

    protected $listeners = ['refreshComponent' => '$refresh', 'confirmProcessStep'];

    public function mount()
    {
        $this->user = auth()->user();
        $this->vendor_add_type = $this->user->vendor->id;
        $this->vendor_users = $this->user->vendor->users()->where('is_employed', 1)->get();
        $this->registration = $this->user->vendor->registration;

        if(is_null($this->user->vendor)){
            return redirect(route('vendor_selection'));
        }

        if($this->vendor->id != $this->user->vendor->id OR $this->registration['registered']){
            return redirect(route('vendor_selection'));
        }

        if($this->user->vendor->distributions->isEmpty()){
            //create OFFICE and admin user distributions
            Distribution::create([
                'vendor_id' => $this->user->vendor->id,
                'name' => 'OFFICE',
                'user_id' => 0
            ]);

            Distribution::create([
                'vendor_id' => $this->user->vendor->id,
                'name' => $this->user->first_name . ' - Home',
                'user_id' => $this->user->id
            ]);
        }

        if($this->user->vendor->company_emails()->exists() AND $this->registration['emails_registered'] == FALSE){
            $this->confirmProcessStep('emails_registered');
        }

        if($this->user->vendor->banks()->exists() AND $this->registration['banks_registered'] == FALSE){
            $this->confirmProcessStep('banks_registered');
        }

        $this->icons['checkmark'] = "M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z";
        $this->icons['vendor'] = "M6 3.75A2.75 2.75 0 018.75 1h2.5A2.75 2.75 0 0114 3.75v.443c.572.055 1.14.122 1.706.2C17.053 4.582 18 5.75 18 7.07v3.469c0 1.126-.694 2.191-1.83 2.54-1.952.599-4.024.921-6.17.921s-4.219-.322-6.17-.921C2.694 12.73 2 11.665 2 10.539V7.07c0-1.321.947-2.489 2.294-2.676A41.047 41.047 0 016 4.193V3.75zm6.5 0v.325a41.622 41.622 0 00-5 0V3.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25zM10 10a1 1 0 00-1 1v.01a1 1 0 001 1h.01a1 1 0 001-1V11a1 1 0 00-1-1H10z M3 15.055v-.684c.126.053.255.1.39.142 2.092.642 4.313.987 6.61.987 2.297 0 4.518-.345 6.61-.987.135-.041.264-.089.39-.142v.684c0 1.347-.985 2.53-2.363 2.686a41.454 41.454 0 01-9.274 0C3.985 17.585 3 16.402 3 15.055z";
        $this->icons['user_add'] = "M11 5a3 3 0 11-6 0 3 3 0 016 0zM2.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 018 18a9.953 9.953 0 01-5.385-1.572zM16.25 5.75a.75.75 0 00-1.5 0v2h-2a.75.75 0 000 1.5h2v2a.75.75 0 001.5 0v-2h2a.75.75 0 000-1.5h-2v-2z";
        $this->icons['user'] = "M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z";
        $this->icons['credit_card'] = "M2.5 4A1.5 1.5 0 001 5.5V6h18v-.5A1.5 1.5 0 0017.5 4h-15zM19 8.5H1v6A1.5 1.5 0 002.5 16h15a1.5 1.5 0 001.5-1.5v-6zM3 13.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm4.75-.75a.75.75 0 000 1.5h3.5a.75.75 0 000-1.5h-3.5z";
        $this->icons['email'] = "M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z";
        $this->icons['distributions'] = "M12 1.5c-1.921 0-3.816.111-5.68.327-1.497.174-2.57 1.46-2.57 2.93V21.75a.75.75 0 0 0 1.029.696l3.471-1.388 3.472 1.388a.75.75 0 0 0 .556 0l3.472-1.388 3.471 1.388a.75.75 0 0 0 1.029-.696V4.757c0-1.47-1.073-2.756-2.57-2.93A49.255 49.255 0 0 0 12 1.5Zm3.53 7.28a.75.75 0 0 0-1.06-1.06l-6 6a.75.75 0 1 0 1.06 1.06l6-6ZM8.625 9a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm5.625 3.375a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Z";
    }

    public function confirmProcessStep($process_step){
        $this->registration[$process_step] = true;
        $this->user->vendor->registration = json_encode($this->registration);
        $this->user->vendor->save();

        return redirect(route('vendor_registration', $this->vendor->id));
    }

    public function addVendorHiveInfo()
    {
        //5-19-2023 ... queue this in case someone EXITS, if job not done and user tries to come back, show the spinning/loading wheel upon login...
        ini_set('max_execution_time', '480000');
        //where vendor is registering initinally or going forward ($vendor->registration->registered = true)
        $vendor = $this->user->vendor;
        $vendor_users_ids = $vendor->users->pluck('id')->toArray();
        $vendor_id = $vendor->id;

        //3-21-2023 this should be one query? $projects_query
        //5-24-2023 .. what about Expense Splits?
        $projects_query_expenses =
            Project::withoutGlobalScopes()
                ->withWhereHas('expenses', function ($query) use ($vendor_id) {
                    $query->withoutGlobalScopes()->where('vendor_id', $vendor_id);
                })->get();

        $projects_query_timesheets =
            Project::withoutGlobalScopes()
                ->withWhereHas('timesheets', function ($query) use ($vendor_users_ids) {
                    $query->withoutGlobalScopes()->whereIn('user_id', $vendor_users_ids);
                })->get();

        // $projects_query =
        //     Project::withoutGlobalScopes()
        //         ->with('timesheets', function ($query) use ($vendor_users_ids) {
        //             $query->withoutGlobalScopes()->whereIn('user_id', $vendor_users_ids)->whereHas('project');
        //         })
        //         ->with('expenses', function ($query) use ($vendor) {
        //             $query->withoutGlobalScopes()->where('vendor_id', $vendor->id)->whereHas('project');
        //         });

        //$projects = $projects_query->get();
        $projects = $projects_query_expenses->merge($projects_query_timesheets);

        //group $projects_query by 'belongs_to_vendor_id',
        $belongs_to_vendors_ids = array_keys($projects->groupBy('belongs_to_vendor_id')->toArray());

        foreach($belongs_to_vendors_ids as $belongs_to_vendor_id){
            //find vendor_id on clients table
            $client = Client::withoutGlobalScopes()->where('vendor_id', $belongs_to_vendor_id)->first();

            //if vendor doesn't have a client
            //When created we need to create a Client associated with this vendor_id
            //5-25-2025 incorporate VendorObserver | similar code
            if(is_null($client)){
                //create client from $this->vendor
                $adding_vendor = Vendor::withoutGlobalScope(VendorScope::class)->findOrFail($belongs_to_vendor_id);

                $client = Client::make();
                $client->business_name = $adding_vendor->business_name;
                $client->address = $adding_vendor->address;
                $client->address_2 = $adding_vendor->address_2;
                $client->city = $adding_vendor->city;
                $client->state = $adding_vendor->state;
                $client->zip_code = $adding_vendor->zip_code;
                $client->home_phone = $adding_vendor->business_phone;
                //attach
                $client->vendor_id = $adding_vendor->id;

                $client->save();
            }

            //attach $vendor->id to this $client (which is linked to a vendor_id / the one we're associating expenses / payments to below)
            $client->vendors()->attach($vendor->id);
        }

        foreach($projects as $project){
            if($project->belongs_to_vendor_id != $vendor->id){
                $vendor_id = $vendor->id;
                $client_id = $client->id;
            }else{
                $vendor_id = $project->belongs_to_vendor_id;
                $client_id = $project->client_id;
            }

            $project->vendors()->attach($vendor_id, ['client_id' => $client_id]);
            app('App\Http\Controllers\VendorRegisteredController')
                ->add_project_status(
                    $project->id,
                    $vendor_id,
                    'VIEW ONLY'
                );
        }

        //PAYMENTS
        $checks = Check::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->with('expenses', function ($query) use ($vendor) {
                $query->withoutGlobalScopes();
            })
            ->with('timesheets', function ($query) use ($vendor) {
                $query->withoutGlobalScopes();
            })->get();

        foreach($checks as $check){
            //check->expenses
            app('App\Http\Controllers\VendorRegisteredController')
                ->create_payment_from_check(
                    $check,
                    $check->expenses,
                    $vendor
                );

            //check->timesheets
            app('App\Http\Controllers\VendorRegisteredController')
                ->create_payment_from_check(
                    $check,
                    $check->timesheets,
                    $vendor
                );
        }

        //BIDS
        $projects = Project::all();

        foreach($projects as $project){
            //if payments MORE than bids
            if($project->finances['payments'] > $project->finances['total_bid']){
                $amount_difference = $project->finances['payments'] - $project->finances['total_bid'];

                //if project has NO Bids... bid type = 1, if more: bid type = 2
                if(!$project->bids()->exists()){
                    $bid_type = 1;
                }else{
                    $bid_type = 2;
                }

                //create vendor/project bid
                Bid::create([
                    'amount' => $amount_difference,
                    'type' => $bid_type,
                    'project_id' => $project->id,
                    'vendor_id' => $vendor_id,
                ]);
            }
        }

        return;
    }

    public function store()
    {
        $this->addVendorHiveInfo();

        //register vendor with user
        $this->user->vendor->registration ='{"registered": true}';
        $this->user->vendor->save();

        return redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.entry.vendor-registration');
    }
}
