<?php

namespace App\Providers;

use App\Models\Quote;
use App\Observers\QuoteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Quote::observe(QuoteObserver::class);
    }
}
