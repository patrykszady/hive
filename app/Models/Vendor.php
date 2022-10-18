<?php

namespace App\Models;

use App\Models\User;
use App\Scopes\VendorScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'business_name', 'business_type', 'address', 'address_2', 'city', 'state', 'zip_code', 'business_phone', 'business_email', 'created_at', 'updated_at'];

    protected static function booted()
    {
        static::addGlobalScope(new VendorScope);
    }

    //Vendors that belong to Logged in vendor / via $user->primary_vendor_id
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendors_vendor', 'belongs_to_vendor_id')->withoutGlobalScopes()->withTimestamps();
    }

    // public function vendor()
    // {
    //     return $this->belongsToMany(Vendor::class, 'vendors_vendor', 'vendor_id')->withTimestamps();
    // }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function banks()
    {
        return $this->hasMany(Bank::class);
    }

    public function bank_accounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    // public function project_status()
    // {
    //     return $this->hasMany(ProjectStatus::class, '');
    // }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->with('vendor');
    }

    public function auth_user_role()
    {
        return $this->belongsToMany(User::class)->withPivot(['is_employed', 'role_id', 'via_vendor_id', 'start_date', 'end_date', 'hourly_rate'])->wherePivot('user_id', auth()->user()->id);
    }

    public function getUserRoleAttribute()
    {
        $role_id = $this->auth_user_role->first()->pivot->role_id;

        if($role_id == 1){
            $role = 'Admin';
        }elseif($role_id == 2){
            $role = 'Member';
        }else{
            $role = 'No Role';
        }

        return $role;
    }

    public function getFullAddressAttribute()
    {
        if($this->address_2){
            $address = $this->address . '<br>' . $this->address_2 . '<br>' . $this->city . ', ' . $this->state . ' ' . $this->zip_code; 
        }elseif($this->address){
            $address = $this->address . '<br>' . $this->city . ', ' . $this->state . ' ' . $this->zip_code; 
        }else{
            $address = NULL;
        }
        
        return $address;
    }

    // public function getOneLineAddressAttribute()
    // {
    //     if($this->address_2){
    //         $address = $this->address . ' | ' . $this->address_2 . ' | ' . $this->city . ', ' . $this->state . ' ' . $this->zip_code;
    //     }elseif($this->address){
    //         $address = $this->address . ' | ' . $this->city . ', ' . $this->state . ' ' . $this->zip_code;
    //     }else{
    //         $address = NULL;
    //     }
        
    //     return $address;
    // }

    public function getAddressMapURI()
    {
        $url = 'https://maps.apple.com/?q=' . $this->address . ', ' . $this->city . ', ' . $this->state . ', ' . $this->zip_code;

        return $url;
    }



    // public function setBusinessName($value)
    // {
    //     // dd($value);
    //     $this->attributes['business_name'] = ucwords($value);
    // }

    // public function businessName(): Attribute
    // {
    //     return Attribute::make(
    //         set: fn ($value) => ucwords($value),
    //     );
    // }
}
