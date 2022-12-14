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

    public function distributions()
    {
        return $this->belongsToMany(Distribution::class)->withPivot('percent', 'amount')->withTimestamps();
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

    public function getFinancesAttribute()
    {
        $expenses_sum = $this->expenses()->where('reimbursment', 'Client')->sum('amount');
        $splits_sum = $this->expenseSplits()->where('reimbursment', 'Client')->sum('amount');

        $finances['estimate'] = $this->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 1)->sum('amount');
        $finances['change_orders'] = $this->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 2)->sum('amount');
        $finances['reimbursments'] = $splits_sum + $expenses_sum;
        $finances['total_project'] = $finances['reimbursments'] + $finances['estimate'] + $finances['change_orders'];
        $finances['expenses'] = $this->expenses->sum('amount') + $this->expenseSplits->sum('amount');
        $finances['timesheets'] = $this->timesheets->sum('amount');
        $finances['total_cost'] = $finances['timesheets'] + $finances['expenses'];
        $finances['payments'] = $this->payments->sum('amount');
        $finances['profit'] = $finances['total_project'] - $finances['total_cost'];
        $finances['balance'] = $finances['total_project'] - $finances['payments'];

        return $finances;
    }

    public function getAddressMapURI()
    {
        $url = 'https://maps.apple.com/?q=' . $this->address . ', ' . $this->city . ', ' . $this->state . ', ' . $this->zip_code;

        return $url;
    }

    public function getNameAttribute()
    {
        if($this->project_name == 'EXPENSE SPLIT' || $this->project_name == 'NO PROJECT'){
            $name = $this->project_name;
        }elseif($this->distribution == TRUE){
            // dd($this->distribution_id);
            $name = $this->project_name;
        }else{
            $name = $this->address . ' | ' . $this->project_name;
        }
        
        return $name;

        // if($this->project_name == 'Expense is Split' || $this->project_name == 'No Project'){
        //     $name = $this->project_name;
        // }else{
        //     $address = $this->address;

        //     //find S/N/W/E and delete it..
        //     //find first space after numbers
        //     //find first space
        //     $last_space_position = strrpos($address, ' ');
        //     $address = substr($address, 0, $last_space_position);

        //     //remove last word of string
        //     $name = $address . ' | ' . $this->project_name;
        // }
    
        // return $name;
    }

    public function scopeActive($query)
    {
        $query->whereHas('project_status', function ($q) {
            $q->where('project_status.title', 'Active');
        });
    }
}
