<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\Vendor;

class TeamMembers extends Component
{
    public Vendor $vendor;
    public $user;
    public $vendor_users = [];

    public $registration = FALSE;

    protected $listeners = ['refreshComponent' => '$refresh', 'fakeRefresh'];

    public function mount()
    {
        $this->user = auth()->user();
        $this->vendor_users = $this->vendor->users()->employed()->get();
    }

    public function fakeRefresh()
    {
        $this->mount();
        $this->render();
    }

    public function render()
    {
        return view('livewire.users.team-members');
    }
}
