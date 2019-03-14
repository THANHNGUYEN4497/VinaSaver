<?php

namespace App\Providers;

use App\Repositories\MailRepository;
use App\Services\Mail\MailService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MailRepository::class, MailService::class);
    }
}
