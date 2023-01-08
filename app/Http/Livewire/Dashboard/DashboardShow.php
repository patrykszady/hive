<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

class DashboardShow extends Component
{
    public function mount()
    {
        $this->user = auth()->user();
        $this->vendor_add_type = $this->user->vendor->id;
    }

    public function render()
    {
        $user = auth()->user();
        $vendor_users = $user->vendor->users()->where('is_employed', 1)->get();

        return view('livewire.dashboard.show', [
            'user' => $user,
            'vendor_users' => $vendor_users,
        ]);
    }
}
