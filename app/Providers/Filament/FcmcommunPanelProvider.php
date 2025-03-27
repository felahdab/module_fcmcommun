<?php

namespace Modules\FcmCommun\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\FontProviders\SpatieGoogleFontProvider;
use App\Filament\AvatarProviders\AnnudefAvatarProvider;
use App\Providers\Filament\Traits\UsesSkeletorPrefixAndMultitenancyTrait;

use App\Http\Middleware\InitializeTenancyByPath;
use App\Http\Middleware\SetTenantCookieMiddleware;
use App\Http\Middleware\SetTenantDefaultForRoutesMiddleware;

use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\Route;
use  Modules\FcmCommun\Helpers\NavigationFilamentHelper;

class FcmcommunPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;

    private string $module = "FcmCommun";
    public function panel(Panel $panel): Panel
    {
        $moduleNamespace = $this->getModuleNamespace();
        return $panel
            ->id('fcmcommun::fcmcommun')
            ->path($this->prefix . '/fcmcommun')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->font('Inter', provider: SpatieGoogleFontProvider::class)
            ->defaultAvatarProvider(AnnudefAvatarProvider::class)
            ->brandName("FCM Commun")
            ->discoverResources(in: module_path($this->module, 'app/Filament/Fcmcommun/Resources'), for: "$moduleNamespace\\Filament\\Fcmcommun\\Resources")
            ->discoverPages(in: module_path($this->module, 'app/Filament/Fcmcommun/Pages'), for: "$moduleNamespace\\Filament\\Fcmcommun\\Pages")
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'Filament/Fcmcommun/Widgets'), for: "$moduleNamespace\\Filament\\Fcmcommun\\Widgets")
            ->widgets([
               // Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                InitializeTenancyByPath::class,
                SetTenantDefaultForRoutesMiddleware::class,
                SetTenantCookieMiddleware::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Menu Side Bar Haut
            ->topNavigation()
            // Appel du Helper pour le Menu Modules
            ->navigationItems([   
                // Decompacte le tableau
                ...NavigationFilamentHelper::registerNavigationsItems()
            ])  

            ;
          
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
