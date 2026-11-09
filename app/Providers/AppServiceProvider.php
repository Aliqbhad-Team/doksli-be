<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use App\Services\JwtService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\Folder;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Observers\FolderObserver;
use App\Observers\DocumentObserver;
use App\Observers\DocumentVersionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }
    public function boot(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider'] ?? 'users');
            $request = $app->make(\Illuminate\Http\Request::class);
            $jwt = $app->make(JwtService::class);

            return new JwtGuard($provider, $request, $jwt);
        });
        Folder::observe(FolderObserver::class);
        Document::observe(DocumentObserver::class);
        DocumentVersion::observe(DocumentVersionObserver::class);
    }
}