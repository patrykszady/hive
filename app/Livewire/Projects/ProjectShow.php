<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectStatus;

use Spatie\Browsershot\Browsershot;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectShow extends Component
{
    use AuthorizesRequests;

    public Project $project;

    public $finances = [];
    public $project_status = NULL;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        //12/16/22 - listener $refresh after we move ProjectStatus to a new Livewire component
        $this->project_status = $this->project->project_status ? $this->project->project_status->title : NULL;
    }

    //7-20-2022 move to ProjectStatus Livewire component
    public function change_project_status()
    {
        if($this->project->project_status){
            //UPDATE ProjectStatus
            //find this project status and change
            $update_project_status = $this->project->project_status;

            $update_project_status->title = $this->project_status;
            $update_project_status->save();
        }else{
            //new ProjectStatus
            $update_project_status = ProjectStatus::create([
                'project_id' => $this->project->id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'title' => $this->project_status,
            ]);
        }

        $update_project_status->refresh();

        $this->dispatch('notify', type: 'success', content: 'Project Status Updated');
    }


    //Reimbursement
    public function print()
    {
        //11-6-2022 QUEUE THIS??
        $this->authorize('view', $this->project);

        $expenses = $this->project->expenses()->where('reimbursment', 'Client')->get();
        $splits = $this->project->expenseSplits()->where('reimbursment', 'Client')->get();

        foreach($expenses as $expense){
            $expense->receipt = $expense->receipts()->latest()->first();
            $expense->receipt_html = $expense->receipt->receipt_html;
            $expense->receipt_filename = $expense->receipt->receipt_filename;
            $expense->business_name = $expense->vendor->business_name;
            $expense->project_name = $expense->project->name;
        }

        foreach($splits as $split){
            $split->receipt = $split->expense->receipts()->latest()->first();
            $split->receipt_html = $split->receipt->receipt_html;
            $split->receipt_filename = $split->receipt->receipt_filename;
            $split->business_name = $split->expense->vendor->business_name;
            $split->date = $split->expense->date;
            $split->project_name = $split->project->name;

            $expenses->add($split);
        }

        $expenses = $expenses->sortBy('date');

        $title = 'Reimbursements | ' .  $this->project->client->name . ' | ' . $this->project->project_name . ' | ' .  $this->project->id;
        $title_file = 'Reimbursements - ' . $this->project->id . ' - ' .  $this->project->client->name . ' - ' . $this->project->project_name;

        $view =  view('misc.print_reimbursments', compact(['expenses', 'title']))->render();
        $location = storage_path('files/reimbursements/' . $title_file . '.pdf');

        Browsershot::html($view)
            ->newHeadless()
            ->scale(0.8)
            ->showBrowserHeaderAndFooter()
            // ->headerHtml('Header')
            // ->footerHtml('<span class="pageNumber"></span>')
            //->margins($top, $right, $bottom, $left)
            ->margins(10, 5, 10, 5)
            ->save($location);

        $headers =
            array(
                'Content-Type: application/pdf',
            );

        return Response::download($location, $title_file . '.pdf', $headers);
    }

    #[Title('Project')]
    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.projects.show', [
        ]);
    }
}
