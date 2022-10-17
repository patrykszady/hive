<?php

namespace App\Models;

use App\Scopes\ProjectScope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'project_name', 'client_id', 'belongs_to_vendor_id', 'created_by_user_id', 'note', 'timesheet_id', 'created_by_user_id', 'note', 'do_not_include', 'address', 'address_2', 'city', 'state', 'zip_code', 'created_at', 'updated_at'];

    protected $appends = ['name'];

    protected static function booted()
    {
        static::addGlobalScope(new ProjectScope);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function expenseSplits()
    {
        return $this->hasMany(ExpenseSplits::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function project_status()
    {
        return $this->hasOne(ProjectStatus::class);
    }

    //7-19-2022: when we make multiple project statuses/stages/timelines again
    // public function getProjectStatusAttribute()
    // {
    //     return $this->project_statuses()->latest()->first()->title;
    // }

    public function getFullAddressAttribute()
    {
        if ($this->address_2 == NULL) {
            $address1 = $this->address;
        } else {
            $address1 = $this->address . '<br>' . $this->address_2;
        }
            $address2 = $this->city . ', ' . $this->state . ' ' . $this->zip_code;

            return $address1 . '<br>' .  $address2;
    }

    public function getAddressMapURI()
    {
        $url = 'https://maps.apple.com/?q=' . $this->address . ', ' . $this->city . ', ' . $this->state . ', ' . $this->zip_code;

        return $url;
    }

    public function getNameAttribute()
    {
        if($this->project_name == 'Expense is Split' || $this->project_name == 'No Project'){
            $name = $this->project_name;
        }else{
            $name = $this->address . ' | ' . $this->project_name;
        }
        
        return $name;
    }

    public function scopeActive($query)
    {
        $query->whereHas('project_status', function ($q) {
            $q->where('project_status.title', 'Active');
        });
    }
}
