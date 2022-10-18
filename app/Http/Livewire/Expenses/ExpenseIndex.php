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
    public $view = NULL;

    protected $queryString = [
        'amount' => ['except' => ''],
        'project' => ['except' => ''],
        'vendor' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    // public function updated($field) 
    // {
    //     if($field == 'vendor'){
    //         dd($this->project);
    //     }
    // }

    public function render()
    {       
        $this->authorize('viewAny', Expense::class);

        $project = $this->project;
        $vendor = $this->vendor;

        if($this->view == NULL){
            $paginate_number = 10;
        }else{
            $paginate_number = 5;
        }

        //11/4/2021 where year, where sort, where date_between.. default date = YTD
        //09/22/22 ... what about searchinf for individual expense_splits?
        $expenses = Expense::orderBy('date', 'DESC')
            ->with(['project', 'distribution', 'vendor', 'splits'])
            ->where('amount', 'like', "%{$this->amount}%")

            ->when($project == 'SPLIT', function ($query) {
                return $query->has('splits');
            })
            ->when($project == 'NO_PROJECT', function ($query) {
                return $query->where('project_id', "0")->whereNull('distribution_id');
            })
            ->when(substr($project, 0, 2) == "D-", function ($query) {
                return $query->where('distribution_id', substr($this->project, 2));
            })
            ->when(is_numeric($project), function ($query, $project) {
                return $query->where('project_id', $this->project);
            })

            ->when($vendor != NULL, function ($query, $vendor) {
                return $query->where('vendor_id', 'like', "{$this->vendor}");
            })

            ->paginate($paginate_number);
        
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


