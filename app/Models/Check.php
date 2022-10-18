<?php

namespace App\Models;

use App\Scopes\CheckScope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Check extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['id', 'check_type', 'check_number', 'date', 'bank_account_id', 'user_id', 'vendor_id', 'belongs_to_vendor_id', 'created_by_user_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = ['date', 'deleted_at'];

    protected $casts = [
        'date' => 'date:Y-m-d'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CheckScope);
    }

    // protected $appends = ['owner'];

    // public function vendor()
    // {
    //     return $this->belongsTo(Vendor::class)->withDefault([
    //         //if transaction->vendor_id == NULL?
    //         'business_name' => 'No Vendor',
    //     ]);
    // }

    // public function expense()
    // {
    //     return $this->belongsTo(Expense::class)->withDefault([
    //         //if transaction->expense_id == NULL?
    //         'id' => 'No Expense',
    //     ]);
    // }

    //has many hours
    
    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checks()
    {
        return $this->hasMany(Check::class);
    }    

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }    

    public function getAmountAttribute()
    {
        //distrubutions too!
        $total = $this->expenses->sum('amount') + $this->timesheets->sum('amount');

        return $total;
    }

    public function getOwnerAttribute()
    {
        if($this->vendor_id){
            $owner = $this->vendor->business_name;
        }elseif($this->user_id){
            $owner = $this->user->full_name;
        }

        return $owner;
    }
}