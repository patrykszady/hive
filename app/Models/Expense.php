<?php

namespace App\Models;

use App\Scopes\ExpenseScope;

use App\Models\ExpenseSplits;
use App\Models\ExpenseReceipts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'amount', 'date', 'invoice', 'note', 'project_id', 'distribution_id', 'vendor_id', 'check_id', 'check_id', 'reimbursment' , 'belongs_to_vendor_id', 'created_by_user_id', 'paid_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = ['date', 'deleted_at'];

    protected $casts = [
        'date' => 'date:Y-m-d'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ExpenseScope);
    }
    
    public function project()
    {
        //if "Expense is SPlit..." ...if project_id = 0 AND NO SPLITS = NO PROJECT..>ENTER!!
        // return $this->belongsTo(Project::class)->withDefault([
        //     //if expense has splits.. otherwise NULL?
        //     'project_name' => 'Expense is Split',
        // ]);

        //1-4-2022 below creates an N + 1 problem
        return $this->belongsTo(Project::class)->withDefault(function ($project, $expense) {
            if($expense->splits()->exists()){
                $project->project_name = 'Expense is Split';
            }else{
                $project->project_name = 'No Project';
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
}
