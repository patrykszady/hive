<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory, SoftDeletes;

    //deleted_at
    protected $fillable = [
        'name',
        'desc',
        'notes',
        'category',
        'sub_category',
        'unit_type',
        'cost',
        'belongs_to_vendor_id',
        'created_at',
        'updated_at'
    ];

    public function estimates()
    {
        return $this->belongsToMany(Estimate::class)->withTimestamps();
    }

    // public function setUnitTypeAttribute($value)
    // {
    //     if($value == 'NULL'){
    //         $this->attributes['unit_type'] = NULL;
    //     }
    // }
}
