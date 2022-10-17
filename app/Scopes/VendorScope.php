<?php

namespace App\Scopes;

use App\Models\Vendor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model)    
    {
        $logged_in_vendor = auth()->user()->vendor;
        //get vendors where belongs_to_vendor_id on vendor_vendors tables = logged_in_vendor_id
        $vendor_ids = $logged_in_vendor->vendors->pluck('id');
        $builder->whereIn('id', $vendor_ids)->orWhere('id', $logged_in_vendor->id);
    }
}