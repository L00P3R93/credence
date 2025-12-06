<?php

namespace App\Observers;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->email_verified_at = null;
        $user->username = Str::slug($user->name);
    }
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if(app()->runningInConsole()){
            return;
        }

        Notification::make()
            ->title('New User Created')
            ->body("User {$user->name} has been created")
            ->icon($user->gravatar_url ?? Heroicon::User)
            ->actions([
                Action::make('View')->url(UserResource::getUrl('edit', ['record' => $user])),
            ])->sendToDatabase(User::role(['Super Admin'])->get());

        /*$name = $user->name;
        $phone = $user->phone;
        $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
        $generatedPassword = "{$firstName}@{$phone}";
        $plainPassword = $generatedPassword;
        Mail::to($user->email)->send(new UserAccountCreated($user, $plainPassword));
        */
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Notification::make()
            ->title('User Updated')
            ->body("User {$user->name} has been updated")
            ->icon($user->gravatar_url ?? Heroicon::User)
            ->actions([
                Action::make('View')->url(UserResource::getUrl('edit', ['record' => $user])),
            ])->sendToDatabase(User::role('Admin')->get());
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
