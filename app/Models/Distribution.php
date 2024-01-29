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

    public function getBalancesAttribute($value)
    {
        $balances = json_decode($value);
        return $balances;
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withPivot('percent', 'amount', 'created_at')->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function receipt_accounts()
    {
        return $this->hasMany(ReceiptAccount::class);
    }

    public function transactions_bulk_match()
    {
        return $this->hasMany(TransactionBulkMatch::class);
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
