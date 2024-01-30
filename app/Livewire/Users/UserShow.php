<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserShow extends Component
{
    use AuthorizesRequests;

    public User $user;
    // public $modal_show = FALSE;

    // protected $listeners = ['showMember'];

    // public function showMember(User $user)
    // {
    //     // $this->modal_show = true;
    //     return view('livewire.users.show', [
    //         'user' => $user,
    //     ]);
    // }
    public function mount()
    {
        $this->user->this_vendor = $this->user->vendors->where('id', auth()->user()->vendor->id)->first();
    }

    #[Title('User')]
    public function render()
    {
        return view('livewire.users.show');
    }
}
