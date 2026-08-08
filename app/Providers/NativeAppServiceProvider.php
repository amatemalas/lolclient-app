<?php

namespace App\Providers;

use App\Services\LeagueClient\LockfilePreferences;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\MenuBar;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        if ($path = app(LockfilePreferences::class)->get()) {
            config(['leagueclient.lockfile_path' => $path]);
        }

        Window::open()
            ->width(1600)
            ->height(900);
        MenuBar::create();
        Menu::create(
            Menu::app()->label('About'),
            Menu::make(
                Menu::link('https://nativephp.com', 'Documentation'),
            )->label('Docs')
        );
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
