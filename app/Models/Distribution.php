<?php

namespace App\Models;

use App\Scopes\DistributionScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new DistributionScope);
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function splits()
    {
        return $this->hasMany(ExpenseSplits::class);
    }
}
