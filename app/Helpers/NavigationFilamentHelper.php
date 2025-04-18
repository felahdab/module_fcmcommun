<?php

namespace Modules\FcmCommun\Helpers;

use Filament\Navigation\NavigationItem;

class NavigationFilamentHelper
{
    public static function registerNavigationsItems()
    {
        return [];
        return [
            NavigationItem::make('RH')
                    ->url(fn () => route('filament.RH.pages.dashboard'))
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn () => request()->routeIs('filament.RH.pages.dashboard'))
                    ->group('Modules')
                    ->sort(1)
                    ->hidden(fn():bool => ! auth()->user()->can('users.store')),
            
                NavigationItem::make('FcmCentral')
                    ->url(fn () => route('filament.FCM Central.pages.dashboard'))
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn () => request()->routeIs('filament.FCM Central.pages.dashboard'))
                    ->group('Modules')
                    ->sort(2)
                    ->hidden(fn():bool => ! auth()->user()->can('users.store')),
                
                NavigationItem::make('FcmUnite')
                    ->url(fn () => route('filament.FCM Unité.pages.dashboard'))
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn () => request()->routeIs('filament.FCM Unité.pages.dashboard'))
                    ->group('Modules')
                    ->sort(3)
                    ->hidden(fn():bool => ! auth()->user()->can('users.store')),
                
                NavigationItem::make('FcmCommun')
                    ->url(fn () => route('filament.fcmcommun::fcmcommun.pages.dashboard'))
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn () => request()->routeIs('filament.fcmcommun::fcmcommun.pages.dashboard'))
                    ->group('Modules')
                    ->sort(4)
                    ->hidden(fn():bool => ! auth()->user()->can('users.store')),
        ];

    }
}
