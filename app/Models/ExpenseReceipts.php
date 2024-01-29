<?php

namespace App\Models;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Carbon\Carbon;

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

    public function getNotesAttribute($value)
    {
        if(!empty($this->receipt_items->handwritten_notes)){
            $handwritten_notes = $this->receipt_items->handwritten_notes;
            $handwritten_notes = implode(' | ', $handwritten_notes);
        }else{
            $handwritten_notes = FALSE;
        }

        if(isset($this->receipt_items->purchase_order)){
            $purchase_order = $this->receipt_items->purchase_order;
        }else{
            $purchase_order = FALSE;
        }

        $notes = array_filter([$handwritten_notes, $purchase_order]);
        $notes = implode(' | ', $notes);

        return $notes;
    }

    public function getReceiptItemsAttribute($value)
    {
        if($value == NULL){
            $receipt_items = NULL;
        }else{
            $receipt_items = json_decode($value);
        }

        return $receipt_items;
    }

    public function getHandwrittenAttribute($value)
    {
        $notes = $this->receipt_items->handwritten_notes;
        if($notes){
            return implode(", ", $notes);
        }else{
            return NULL;
        }
    }

    public function getReceiptDateAttribute($value)
    {
        if(is_string($this->receipt_items->transaction_date)){
            $date = Carbon::parse($this->receipt_items->transaction_date);
        }else{
            $date = Carbon::parse($this->receipt_items->transaction_date->valueDate);
            // if(is_string($this->receipt_items->transaction_date)){
            //     $date = Carbon::parse($this->receipt_items->transaction_date->valueDate);
            // }else{
            //     $date = Carbon::parse($this->receipt_items->transaction_date->valueDate);
            // }
        }

        return $date;
    }

    public function getTaxAttribute($value)
    {

        try {
            $this_subtotal = $this->subtotal;
        } catch (\Exception $e) {
            $this_subtotal = NULL;
        }

        if(is_string($this->receipt_items->total_tax) || is_float($this->receipt_items->total_tax)){
            $tax = $this->receipt_items->total_tax;
        }else{
            if(isset($this->receipt_items->total_tax->valueNumber)){
                $tax = $this->receipt_items->total_tax->valueNumber;
            }else{
                if(isset($this->total) && !is_null($this_subtotal)){
                    $tax = $this->total - $this->subtotal;
                }else{
                    $tax = FALSE;
                }
            }
        }

        return $tax;
    }

    public function getSubtotalAttribute($value)
    {
        if($this->receipt_items->subtotal){
            // dd(is_numeric($this->receipt_items->subtotal));
            // if(is_string($this->receipt_items->subtotal) || is_float($this->receipt_items->subtotal)){
            if(is_numeric($this->receipt_items->subtotal)){
                $subtotal = $this->receipt_items->subtotal;
            }else{
                $subtotal = $this->receipt_items->subtotal->valueNumber;
            }
        }else{
            if(isset($this->total) && isset($this->tax)){
                $subtotal = $this->total - $this->tax;
            }else{
                $subtotal = FALSE;
            }
        }

        return $subtotal;
    }

    public function getTotalAttribute($value)
    {
        if($this->receipt_items->total){
            $total = $this->receipt_items->total;
        }else{
            $total = FALSE;
        }

        return $total;
    }
}
