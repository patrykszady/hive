<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstimateSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['estimate_id', 'index', 'name', 'total', 'bid_id', 'created_at', 'updated_at', 'deleted_at'];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function estimate_line_items()
    {
        return $this->hasMany(EstimateLineItem::class);
    }

    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }
}
