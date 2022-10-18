<?php

namespace App\Models;

use App\Scopes\BankScope;

use App\Models\Vendor;
use App\Models\BankAccount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'plaid_access_token', 'plaid_item_id', 'vendor_id', 'plaid_ins_id', 'plaid_options', 'created_at', 'updated_at', 'deleted_at'];

    protected static function booted()
    {
        static::addGlobalScope(new BankScope);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function accounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}