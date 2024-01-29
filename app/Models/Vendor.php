<?php

namespace App\Models;

use App\Models\User;

use App\Models\Scopes\VendorScope;
use App\Models\Scopes\ClientScope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['business_name', 'business_type', 'sheets_type', 'address', 'address_2', 'city', 'state', 'zip_code', 'business_phone', 'business_email', 'created_at', 'updated_at'];

    protected $appends = ['name'];

    protected static function booted()
    {
        static::addGlobalScope(new VendorScope);
    }

    //Vendors that belong to Logged in vendor / via $user->primary_vendor_id
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendors_vendor', 'belongs_to_vendor_id')->withoutGlobalScopes()->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function estimates()
    {
        return $this->belongsToMany(Estimate::class)->withTimestamps();
    }

    public function vendor()
    {
        return $this->belongsToMany(Vendor::class, 'vendors_vendor', 'vendor_id')->withTimestamps();
    }

    public function receipt_accounts()
    {
        return $this->hasMany(ReceiptAccount::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function transactions_bulk_match()
    {
        return $this->hasMany(TransactionBulkMatch::class);
    }

    public function company_emails()
    {
        return $this->hasMany(CompanyEmail::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function vendor_docs()
    {
        return $this->hasMany(VendorDoc::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function banks()
    {
        return $this->hasMany(Bank::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
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
        return $this->belongsToMany(User::class)->with('vendor')->withPivot(['is_employed', 'role_id', 'via_vendor_id', 'start_date', 'end_date', 'hourly_rate']);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class)->withoutGlobalScope(ClientScope::class);
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

    public function getViaVendorAttribute()
    {
        $via_vendor = $this->auth_user_role->first()->pivot->via_vendor_id;

        return $via_vendor;
    }

    public function getRegistrationAttribute($value)
    {
        $value = json_decode($value, true);
        $status_array = ['registered', 'vendor_info', 'team_members', 'user_registered', 'banks_registered', 'emails_registered'];

        foreach($status_array as $status){
            if(!isset($value[$status])){
                $value[$status] = false;
            }
        }

        return $value;
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

    public function getNameAttribute()
    {
        if($this->biz_type == 4 AND !is_null($this->users()->first())){
            $name = $this->users()->first()->first_name . ' ' . $this->users()->first()->last_name;
            return $name;
        }else{
            //delete. INC, DBA..and if it's too long
            $name = explode(",",$this->business_name);
            return $name[0];
        }
    }

    public function getAddressMapURI()
    {
        $url = 'https://maps.apple.com/?q=' . $this->address . ', ' . $this->city . ', ' . $this->state . ', ' . $this->zip_code;

        return $url;
    }


    public function scopeHiveVendors($query)
    {
        return $query->withoutGlobalScopes()->where('business_type', 'Sub')->where('registration->registered', true);
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
