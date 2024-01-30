<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Scopes\TransactionScope;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    // protected $dates = ['transaction_date', 'posted_date', 'date', 'deleted_at'];

    protected $guarded = [];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'transaction_date' => 'date:Y-m-d',
        'posted_date' => 'date:Y-m-d',
        'details' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TransactionScope);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withDefault([
            //if transaction->vendor_id == NULL?
            'business_name' => 'No Vendor',
        ]);
    }

    public function expense()
    {
        // return $this->belongsTo(Expense::class)->withDefault([
        //     //if transaction->expense_id == NULL?
        //     'id' => 'No Expense',
        // ]);
        return $this->belongsTo(Expense::class);
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    // public function accountOwner()
    // {
    //     return $this->hasOneThrough(Bank::class, BankAccount::class);
    // }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function check()
    {
        return $this->belongsTo(Check::class);
    }

    //bank_accountBank
    // public function bank()
    // {
    //     return $this->hasOneThrough(BankAccount::class, Bank::class);
    // }

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
