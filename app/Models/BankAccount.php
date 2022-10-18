<?php

namespace App\Models;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getNameAndType()
    {
        return $this->bank->name . ' | ' . $this->type;
    }
    
    //4-11-2022 accout_number setter... if 3 digits, add 0 in front, if 4 ignore
}
