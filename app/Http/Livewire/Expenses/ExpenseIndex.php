<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Distribution;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $amount = '' ;
    public $project = '';
    public $vendor = '';
    public $status = NULL;
    public $view = NULL;

    protected $queryString = [
        'amount' => ['except' => ''],
        'project' => ['except' => ''],
        'status' => ['except' => ''],
        'vendor' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    public function updated($field) 
    {
        // $this->resetPage();
        // if($field == 'status'){
        //     // dd($this->status);
        // }
    }

    // public function clickExpense(Expense $expense)
    // {
    //     // dd('clickExpense');
    //     $this->emit('showExpense');
    //     // dd($expense);
    //     //emit showExpense expenses.show

    //     //open expense.show modal
    // }

    public function render()
    {       
        $this->authorize('viewAny', Expense::class);

        if($this->view == NULL){
            $paginate_number = 10;
        }else{
            $paginate_number = 5;
        }

        // $expense_ids_excluded = [];
        //11/4/2021 where year, where sort, where date_between.. default date = YTD
        //09/22/22 ... what about searchinf for individual expense_splits?
        $expenses = Expense::orderBy('date', 'DESC')
            ->with(['project', 'distribution', 'vendor', 'splits', 'transactions'])
            ->where('amount', 'like', "%{$this->amount}%")
            ->when($this->project == 'SPLIT', function ($query) {
                return $query->has('splits');
            })
            ->when($this->project == 'NO_PROJECT', function ($query, $item) {
                // dd($query);
                // $expense_ids_excluded += $
                return $query->where('project_id', "0")->whereNull('distribution_id');
            })
            ->when(substr($this->project, 0, 2) == "D-", function ($query) {
                return $query->where('distribution_id', substr($this->project, 2));
            })
            ->when(is_numeric($this->project), function ($query, $project) {
                return $query->where('project_id', $this->project);
            })
            ->when($this->vendor != NULL, function ($query, $vendor) {
                return $query->where('vendor_id', 'like', "{$this->vendor}");
            })
            // ->when($this->status == "Complete", function ($query) {
            //     //->orWhere('distribution_id', '!=', NULL)
            //     $query->where('project_id', '!=', "0")->orWhere('check_id', '!=', NULL);
            // })
            // ->when($this->status == "Missing", function ($query) {
            //     $query->where('project_id', "0")->orDoesntHave('transactions');
            // })
            // ->when(isset($this->status), function ($query) {
            //     //calculate if expense is complete 
            //     if($this->status == "Complete"){

            //         //takes care of not showing NO_PROJECT and expenses with a CHECK
            //         // $query->has('transactions');

            //         // $query = $query->has('transactions');
            //         // $query->where('project_id', '!=', "0")->where('distribution_id', '!=', NULL);

            //         //need to account for has('transactions')

            //         //andWhere orWhere
            //         //->where('project_id', '!=', "0")
                    
            //         //->orWhere('check_id', '!=', NULL)

            //         //->orWhereNotNull('check_id')

            //         // ->orWhere(function($query){
            //         //     $query->has('transactions');
            //         // })

            //         // $query->has('transactions');

            //         //where $expense has project AND where X or Y
            //         // return $query->where(function($query){
            //         //     $query->where('check_id', '!=', NULL)->where(function($query){
            //         //         $query->where('project_id', '!=', "0");
            //         //     });
            //         // });   

            //     }
                
            //     // elseif($this->status == "Missing"){
            //     //     return $query->where('project_id', "0");
            //     // }               
            // })
            
            //$expenes must be a query NOT a collection
            ->paginate($paginate_number);

        
        // dd($expenses);

        //calculate if expense is complete 
        // $expense->transactions->isNotEmpty() && $expense->project != '0' ? 'Complete' : 'Missing Info'
        $expenses->getCollection()->each(function ($expense, $key) use ($expenses){
                // || isset($expense->paid_by)
                if($expense->project_id != "0" && ($expense->transactions->isNotEmpty() || isset($expense->check_id))){
                    // if($this->status == "Missing"){
                    //     //exclude from collection
                    //     $expenses->getCollection()->forget($key);
                    // }

                    $expense->complete = TRUE;
                }else{
                    // if($this->status == "Complete"){
                    //     //exclude from collection
                    //     $expenses->getCollection()->forget($key);
                    // }
                    
                    $expense->complete = FALSE;
                }
            });

        // dd($expenses);
        
        // 11/4/2021 if project is selected only query vendors that that have expenses for that project. if vendor is selected only query projects that have expenses from that vendor... date, amount, etc.
        $projects = Project::whereHas('expenses')->orderBy('created_at', 'DESC')->get();
        $distributions = Distribution::all();
        $vendors = Vendor::whereHas('expenses')->orderBy('business_name')->get();
        
        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'projects' => $projects,
            'distributions' => $distributions,
            'vendors' => $vendors,
        ]);
    }
}


