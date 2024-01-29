<?php

namespace App\Livewire\Forms;

use App\Models\Project;

use Livewire\Attributes\Rule;
use Livewire\Form;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectForm extends Form
{
    use AuthorizesRequests;

    #[Rule('required', as: 'client')]
    public $client_id = NULL;

    #[Rule('required|min:3', as: 'project name')]
    public $project_name = NULL;

    #[Rule('required|min:3')]
    public $address = NULL;

    #[Rule('nullable|min:2')]
    public $address_2 = NULL;

    #[Rule('required|min:3')]
    public $city = NULL;

    #[Rule('required|min:2|max:2')]
    public $state = NULL;

    #[Rule('required|digits:5', as: 'zip code')]
    public $zip_code = NULL;

    #[Rule('required', as: 'address')]
    public $project_existing_address = NULL;

    public function store()
    {
        $this->validate();

        return Project::create([
            'project_name' => $this->project_name,
            'client_id' => $this->client_id,
            'address' => $this->address,
            'address_2' => $this->address_2,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ]);
    }
}
