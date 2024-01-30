<?php

namespace App\Models\Scopes;

// use App\Models\Vendor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if(auth()->guest()){
            // if(!request()->routeIs('transaction_vendor_bulk_match')){
            //     return redirect(route('index'));
            // }
        }else{
            // $builder->whereHas('vendors', function($q){
            //     $q->where('vendor_id', '=', auth()->user()->vendor->id);
            // });

            $logged_in_vendor = auth()->user()->vendor;

            //get vendors where belongs_to_vendor_id on vendor_vendors tables = logged_in_vendor_id
            if(is_null($logged_in_vendor)){
                // redirect(route('dashboard'));
            }else{
                $vendor_ids = $logged_in_vendor->vendors->pluck('id');
                $builder->whereIn('vendors.id', $vendor_ids)->orWhere('vendors.id', $logged_in_vendor->id);
            }
        }
    }
}
