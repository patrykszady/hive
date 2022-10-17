<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['transaction_date', 'posted_date', 'deleted_at'];

    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withDefault([
            //if transaction->vendor_id == NULL?
            'business_name' => 'No Vendor',
        ]);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class)->withDefault([
            //if transaction->expense_id == NULL?
            'id' => 'No Expense',
        ]);
    }
    
    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function check()
    {
        return $this->belongsTo(Check::class);
    }

    public function bank_accountBank()
    {
        return $this->hasOneThrough(Bank::class, BankaAccount::class);
    }

    //used in TransactionController::add_vendor_to_transactions
    //used in Livewire/Transactions/MatchVendor::mount
    public function scopeTransactionsSinVendor($query)
    {
        $query->withoutGlobalScopes()
            ->whereNull('vendor_id')
            ->whereNull('deposit')
            ->whereNull('check_number')
            ->whereNull('deleted_at');
    }
}