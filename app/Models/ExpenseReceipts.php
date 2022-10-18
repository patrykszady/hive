<?php

namespace App\Models;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseReceipts extends Model
{
    use HasFactory;

    protected $table = 'expense_receipts_data';

    protected $guarded = [];

    // protected $fillable = ['expense_id', 'receipt_html' , 'receipt_filename'];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
