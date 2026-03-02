<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->login(Login::class)
            ->defaultThemeMode(ThemeMode::Dark)
            ->darkMode()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Teal,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'info' => Color::Cyan,
                'success' => Color::Green,
                'gray' => Color::Gray,
                'yellow' => Color::Yellow,
                'blue' => Color::Blue,
                'indigo' => Color::Indigo,
                'purple' => Color::Purple,
                'pink' => Color::Pink,
                'green' => Color::Green,
                'cyan' => Color::Cyan,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->widgets([
                //AccountWidget::class,
                //FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                'Leads & Customers',
                'Loan Management',
                'Payments',
                'Banks & Branches',
                'User Management',
                'System Management'
            ])
            ->navigationItems([
                NavigationItem::make('System Logs')
                    ->url('/log-viewer')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->group('System Management')
                    ->sort(50)
                    ->visible(fn () => auth()->user()?->hasRole('Super Admin')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
