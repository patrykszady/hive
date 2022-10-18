<?php

namespace App\Models;

use App\Scopes\PaymentScope;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['id', 'project_id', 'amount', 'date', 'reference', 'transaction_id', 'belongs_to_vendor_id', 'parent_client_payment_id', 'note', 'created_by_user_id', 'created_at', 'updated_at'];
    
    protected $dates = ['date'];

    protected $casts = [
        'date' => 'date:Y-m-d'
    ];  

    protected static function booted()
    {
        static::addGlobalScope(new PaymentScope);
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}