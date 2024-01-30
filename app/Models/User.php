<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword;
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
        'first_name',
        'last_name',
        'cell_phone',
        'email',
        'password',
        'email_verified_at',
        'primary_vendor_id',
        'remember_token',
        'created_at',
        'updated_at',
        'hourly_rate',
        'role',
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
        return $this->belongsToMany(Vendor::class)->using(UserVendor::class)->withoutGlobalScopes()->withTimestamps()->with('vendor')->withPivot(['is_employed', 'role_id', 'via_vendor_id', 'start_date', 'end_date', 'hourly_rate']);
    }

    //User's default/logged in vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'primary_vendor_id')->withoutGlobalScopes();
    }

    //via_vendor
    public function via_vendor()
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

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    public function getVendorRoleAttribute()
    {
        $vendor_id = $this->pivot->vendor_id;

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

    public function getVendorRole($vendor_id)
    {
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

    public function getRegistrationAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    //on vendor->user queries
    public function scopeEmployed($query)
    {
        return $query->where('is_employed', 1);
    }
}
