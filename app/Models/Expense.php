<?php

namespace App\Models;

use App\Scopes\ExpenseScope;

use App\Models\ExpenseSplits;
use App\Models\ExpenseReceipts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['amount', 'date', 'invoice', 'note', 'project_id', 'distribution_id', 'vendor_id', 'check_id', 'reimbursment' , 'belongs_to_vendor_id', 'created_by_user_id', 'paid_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'deleted_at' => 'date:Y-m-d',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ExpenseScope);
    }

    public function project()
    {
        //1-4-2022 below creates an N + 1 problem
        return $this->belongsTo(Project::class)->withDefault(function ($project, $expense) {
            if($expense->splits()->exists()){
                $project->project_name = 'EXPENSE SPLIT';
            }elseif($expense->distribution){
                $project->project_name = $expense->distribution->name;
                $project->distribution = TRUE;
            }else{
                $project->project_name = 'NO PROJECT';
                //1/3/2022 else shoud behave as regular belongsTo method with no withDefault()
                // throw new \Exception("Attempt to read property project_name on null");
            }
        });
    }

    public function check()
    {
        return $this->belongsTo(Check::class)->with('expenses');
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withDefault(function ($expense, $vendor) {
            if($expense->vendor_id === 0){
                $vendor->business_name = 'NO VENDOR';
            }
        });
    }

    public function paidby()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function splits()
    {
        return $this->hasMany(ExpenseSplits::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function receipts()
    {
        return $this->hasMany(ExpenseReceipts::class);
    }

    public function associated()
    {
        // dd('in Expense.php associated() function');
        return $this->hasMany(Expense::class, 'id', 'parent_expense_id');
    }

    public function getAssociatedExpensesAttribute()
    {
        // dd($this->associated->isEmpty());
        if($this->associated->isEmpty()){
            $associated_check = Expense::where('parent_expense_id', $this->id)->get();
            // dd($associated_check);
            if(!$associated_check->isEmpty()){
                return $associated_check;
            }else{
                return NULL;
            }
        }else{
            return $this->associated;
        }
    }

    // protected function reimbursment(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (string $value) => is_numeric($value) ? User::findOrFail($value)->first_name : $value,
    //     );
    // }

    // public function getreimbursmentAttribute()
    // {
    //     dd($this);
    //     if(is_numeric($this->reimbursment)){
    //         return $this->reimbursment = User::findOrFail($this->reimbursment)->first_name;
    //     }else{
    //         return $this->reimbursment;
    //     }
    // }
}
