<?php

namespace App\Models;

use App\Models\Scopes\ReceiptAccountScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new ReceiptAccountScope);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
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
