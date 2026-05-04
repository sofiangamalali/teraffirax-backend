<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Eloquent\CreatorRepository;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Repositories\Eloquent\VendorRepository;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Interfaces\TrackingLinkRepositoryInterface;
use App\Repositories\Eloquent\TrackingLinkRepository;
use App\Repositories\Interfaces\DailyClickRepositoryInterface;
use App\Repositories\Eloquent\DailyClickRepository;
use App\Repositories\Interfaces\FailedDailyClickJobRepositoryInterface;
use App\Repositories\Eloquent\FailedDailyClickJobRepository;
use App\Repositories\Interfaces\TelegramChannelRepositoryInterface;
use App\Repositories\Eloquent\TelegramChannelRepository;
use App\Repositories\Interfaces\WithdrawMethodRepositoryInterface;
use App\Repositories\Eloquent\WithdrawMethodRepository;
use App\Repositories\Interfaces\CreatorWithdrawMethodRepositoryInterface;
use App\Repositories\Eloquent\CreatorWithdrawMethodRepository;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Eloquent\WithdrawalRepository;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Eloquent\CountryRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Interfaces\OtpRepositoryInterface;
use App\Repositories\Eloquent\OtpRepository;
use App\Services\SlugService;
use App\Services\TrackingLinkService;
use App\Services\DailyClickService;
use App\Models\Creator;
use App\Observers\CreatorObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CreatorRepositoryInterface::class, CreatorRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, VendorRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(TrackingLinkRepositoryInterface::class, TrackingLinkRepository::class);
        $this->app->bind(DailyClickRepositoryInterface::class, DailyClickRepository::class);
        $this->app->bind(FailedDailyClickJobRepositoryInterface::class, FailedDailyClickJobRepository::class);
        $this->app->bind(TelegramChannelRepositoryInterface::class, TelegramChannelRepository::class);
        $this->app->bind(WithdrawMethodRepositoryInterface::class, WithdrawMethodRepository::class);
        $this->app->bind(CreatorWithdrawMethodRepositoryInterface::class, CreatorWithdrawMethodRepository::class);
        $this->app->bind(WithdrawalRepositoryInterface::class, WithdrawalRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(OtpRepositoryInterface::class, OtpRepository::class);

        $this->app->singleton(SlugService::class);
        $this->app->singleton(TrackingLinkService::class);
        $this->app->singleton(DailyClickService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Creator::observe(CreatorObserver::class);
    }
}
