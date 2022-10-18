<?php

namespace App\Models;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseSplits extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table="expense_splits";

    protected $fillable = ['amount', 'note', 'project_id', 'distribution_id', 'expense_id', 'reimbursment' , 'belongs_to_vendor_id', 'created_by_user_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = ['deleted_at'];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new ExpenseScope);
    // }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }
}
