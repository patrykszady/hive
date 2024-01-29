<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        if(request()->route()->action['as'] == 'projects.show'){
            $vendor_id = request()->route()->project->client->vendor_id;
        }elseif(request()->route()->action['as'] == 'payments.create'){
            $vendor_id = request()->route()->client->vendor_id;
        }else{
            $vendor_id = TRUE;
        }

        if($vendor_id){
            return false;
        }else{
            return true;
        }
    }
}
