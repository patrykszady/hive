<?php

namespace App\Models;

use App\Models\Scopes\VendorDocScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDoc extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'vendor_id', 'effective_date', 'expiration_date', 'number', 'belongs_to_vendor_id', 'doc_filename', 'created_at', 'updated_at'];
    // protected $dates = ['effective_date', 'expiration_date'];

    protected $casts = [
        'effective_date' => 'date:Y-m-d',
        'expiration_date' => 'date:Y-m-d'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new VendorDocScope);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function getTypeAttribute($value)
    {
        return ucfirst($value);
    }
}
