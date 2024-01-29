<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionBulkMatch extends Model
{
    use HasFactory;

    protected $table = 'transactions_bulk_match';
    protected $fillable = ['amount', 'vendor_id', 'distribution_id', 'belongs_to_vendor_id', 'created_at', 'updated_at', 'options'];

    protected $casts = [
        'options' => 'array',
    ];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function belongs_to_vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
