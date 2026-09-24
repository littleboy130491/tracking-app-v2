<?php

/**
 * File: app/Providers/Filament/AdminPanelProvider.php
 * Responsibility: Configures the admin panel.
 * What it does:
 * - Registers the panel, its brand (style.md colour and font, assets/logo.png),
 *   resource/page/widget discovery, and the Filament plugins in use.
 * - Curator provides the media library and the attachment picker used for
 *   shipment documents and photos; see App\Models\Attachment.
 * How to use: automatic; Filament boots it.
 * How to extend: add further plugins to the plugins() array.
 */

namespace App\Providers\Filament;

use Awcodes\Curator\CuratorPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            // Brand: colour and font come from style.md, the mark from assets/logo.png
            // (published to public/images/logo.png). See resources/css/app.css for
            // the matching customer-portal tokens.
            ->brandName(fn (): string => config('app.name', 'Tracking'))
            ->brandLogo(fn (): string => asset('images/logo.png'))
            ->brandLogoHeight('2rem')
            ->font('Roboto')
            ->colors([
                'primary' => Color::hex('#499bff'),
            ])
            // style.md: all admin pages render full width by default.
            ->maxContentWidth(Width::Full)
            // Sidebar order: the shipment and container entities first, then
            // the supporting groups. Resources declare their group by name.
            ->navigationGroups([
                NavigationGroup::make('Bill of Ladings'),
                NavigationGroup::make('Containers'),
                NavigationGroup::make('CRM'),
                NavigationGroup::make('Master data'),
                NavigationGroup::make('Monitoring'),
            ])
            ->unsavedChangesAlerts()
            ->sidebarFullyCollapsibleOnDesktop()
            ->plugins([
                CuratorPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
