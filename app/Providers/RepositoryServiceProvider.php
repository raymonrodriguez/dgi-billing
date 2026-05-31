<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Contratos
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DgiiTokenRepositoryInterface;
use App\Repositories\Contracts\EcfRepositoryInterface;
// Implementaciones Eloquent
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\DgiiTokenRepository;
use App\Repositories\Eloquent\EcfRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registra los enlaces de las interfaces con sus implementaciones de base de datos.
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(DgiiTokenRepositoryInterface::class, DgiiTokenRepository::class);
        $this->app->bind(EcfRepositoryInterface::class, EcfRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
