<?php
namespace App\Providers;

use App\Observers\ActivityObserver;
use App\Repositories\CarRepository;
use App\Repositories\Contracts\CarRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CarRepositoryInterface::class, CarRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Activity::observe(ActivityObserver::class);
        Activity::created(function ($activity) {
            broadcast(new \App\Events\ActivityLogCreated($activity));
        });

    }
}
