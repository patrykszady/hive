<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'cell_phone',
        'email',
        'password',
        'email_verified_at',
        'primary_vendor_id',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //Vednors USER belongs to
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class)->withoutGlobalScopes()->withTimestamps()->withPivot(['is_employed', 'role_id', 'via_vendor_id', 'start_date', 'end_date', 'hourly_rate']);
    }

    //User's default/logged in vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'primary_vendor_id')->withoutGlobalScopes();
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class)->withTimestamps();
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function getVendorRoleAttribute()
    {
        //get requesting URI dashboard or vendor...
        //if dashboard vendors.id = auth()->user()->vendor->id
        //if vendor vendors.id = vendors/XX  (route)
        if(Route::is('dashboard')){
            $vendor_id = auth()->user()->vendor->id;
        }else{
            $vendor_id = request()->route()->parameters['vendor']['id'];
        }

        $role_id = $this->vendors()->where('vendors.id', $vendor_id)->first()->pivot->role_id;

        if($role_id == 1){
            $role = 'Admin';
        }elseif($role_id == 2){
            $role = 'Member';
        }else{
            $role = 'No Role';
        }

        return $role;
    }

    //NOW IN VENDOR MODEL getUserRoleAttribute
    // public function getVendorRoleAttribute()
    // {
    //     $role_id = $this->pivot->role_id;

    //     if($role_id == 1){
    //         $role = 'Admin';
    //     }elseif($role_id == 2){
    //         $role = 'Member';
    //     }else{
    //         $role = 'No Role';
    //     }

    //     return $role;
    // }
    
    // public function getRoleIdAttribute()
    // {
    //     //if user has vendor, get auth_vendor_Id..if not..NULL
    //     if($this->vendor){
    //         $auth_vendor_id = $this->vendor->id;

    //         dd($auth_vendor_id);
    //         $role_id = $this->vendors()->where('vendor_id', $auth_vendor_id)->first()->pivot->role_id;
    //     }else{
    //         $role_id = NULL;
    //     }

    //     return $role_id;
    // }

// public function getVendorUserRole($vendor)
//     {
//         $role_id = $this->vendors()->where('vendor_id', $vendor->id)->first()->pivot->role_id;
//         if($role_id == 1){
//             $role = 'Admin';
//         }elseif($role_id == 2){
//             $role = 'Member';
//         }else{
//             $role = 'No Role';
//         }

//         return $role;
//     }



    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
