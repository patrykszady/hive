<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function company_email()
    {
        return $this->belongsTo(CompanyEmail::class);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }
}
