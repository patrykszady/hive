<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEmail extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function receipt_accounts()
    {
        return $this->hasMany(ReceiptAccount::class);
    }
}
