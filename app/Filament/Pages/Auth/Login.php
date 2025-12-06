<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'sntaksolutionsltd@gmail.com',
            'password' => 'Sntaks@0727796831',
            'remember' => true
        ]);
    }
}
