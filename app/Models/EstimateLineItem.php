<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstimateLineItem extends Pivot
// class EstimateLineItem extends Model
{
    use HasFactory, SoftDeletes;
    //via_vendor
    // public function via_vendor()
    // {
    //     return $this->belongsTo(Vendor::class, 'via_vendor_id')->withoutGlobalScopes();
    // }
    public function estimate()
    {
        return $this->belongsTo(Estimate::class)->withTimestamps();
    }

    public function section()
    {
        return $this->belongsTo(EstimateSection::class);
    }
}
