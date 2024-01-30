<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'business_name', 'address', 'phone', 'email'];

    public function vendor_docs()
    {
        return $this->hasMany(VendorDoc::class);
    }
}
