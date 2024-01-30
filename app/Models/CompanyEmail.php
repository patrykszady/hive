<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Scopes\CompanyEmailsScope;

class CompanyEmail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyEmailsScope);
    }

    public function receipt_accounts()
    {
        return $this->hasMany(ReceiptAccount::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getApiJsonAttribute($value)
    {
        return json_decode($value, true);
    }
}
