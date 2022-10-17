<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UsersShow extends Component
{
    use AuthorizesRequests;

    public User $user;
    public $modal_show = null;

    protected $listeners = ['showMember'];

    public function showMember(User $user)
    {
        // $this->modal_show = true;
        return view('livewire.users.show', [
            'user' => $user,
        ]);
    }

    public function render()
    {    
        return view('livewire.users.show');
    }
}
