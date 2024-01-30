<?php

namespace App\Models;

use App\Scopes\CheckScope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Check extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['check_type', 'check_number', 'date', 'bank_account_id', 'user_id', 'vendor_id', 'belongs_to_vendor_id', 'created_by_user_id', 'created_at', 'updated_at', 'deleted_at', 'amount'];

    // protected $dates = ['date', 'deleted_at'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CheckScope);
    }

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

    public function getOwnerAttribute()
    {
        //$vendor_id = belongs_to_user ($user_id) //distribution of user that belongs to vendor_id
        if($this->vendor_id && $this->user_id){
            $owner = $this->user->full_name;
        }elseif($this->vendor_id){
            $owner = $this->vendor->business_name;
        }elseif($this->user_id){
            $owner = $this->user->full_name;
        }else{
            $owner = NULL;
        }

        return $owner;
    }
}
