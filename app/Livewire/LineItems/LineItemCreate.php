<?php

namespace App\Livewire\LineItems;

use App\Models\Estimate;
use App\Models\LineItem;
use App\Models\EstimateLineItem;

use Livewire\Component;
use App\Livewire\Forms\LineItemForm;


class LineItemCreate extends Component
{
    public ?Estimate $estimate;

    public LineItemForm $form;

    public $view_text = [
        'card_title' => 'Add Line Item',
        'button_text' => 'Add Item',
        'form_submit' => 'save',
    ];

    public $modal_show = FALSE;
    // 'editOnEstimate', 'removeFromEstimate', 'resetModal'
    // 'addToEstimate'
    protected $listeners = ['addItem', 'editItem'];

    public function resetModal()
    {
        $this->form->reset();
        $this->resetValidation();
    }

    public function addItem()
    {
        $this->resetModal();
        $this->view_text = [
            'card_title' => 'Add Line Item',
            'button_text' => 'Add Item',
            'form_submit' => 'save',
        ];

        $this->modal_show = TRUE;
    }

    // public function addToEstimate(Estimate $estimate, $section)
    // {
    //     $this->estimate = $estimate;

    //     $this->view_text = [
    //         'card_title' => 'Add Line Item',
    //         'button_text' => 'Add Item',
    //         'form_submit' => 'save_estimate',
    //     ];

    //     $this->modal_show = TRUE;
    // }

    public function editItem($lineItemId)
    {
        $this->resetModal();
        $this->view_text = [
            'card_title' => 'Edit Line Item',
            'button_text' => 'Edit Item',
            'form_submit' => 'edit',
        ];

        $line_item = LineItem::findOrFail($lineItemId);
        $this->form->setLineItem($line_item);

        $this->modal_show = TRUE;
    }

    public function save()
    {
        $this->form->store();
        $this->modal_show = FALSE;
        $this->dispatch('refreshComponent')->to('line-items.line-items-index');
    }

    public function edit()
    {
        $this->form->update();
        $this->modal_show = FALSE;
        $this->dispatch('refreshComponent')->to('line-items.line-items-index');
    }

    public function render()
    {
        //08-26-2023 paginate here
        return view('livewire.line-items.form',[
            // 'view_text' => $view_text,
            // 'line_items' => LineItem::all(),
        ]);
    }
}
