<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

class DashboardShow extends Component
{
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
