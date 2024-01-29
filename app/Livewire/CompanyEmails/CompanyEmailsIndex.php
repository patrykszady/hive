<?php

namespace App\Livewire\CompanyEmails;

use App\Models\CompanyEmail;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyEmailsIndex extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public $view = NULL;

    #[Title('Email Accounts')]
    public function render()
    {
        $this->authorize('viewAny', CompanyEmail::class);

        return view('livewire.company-emails.index', [
            'emails' => CompanyEmail::all()
        ]);
    }
}
