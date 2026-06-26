<?php

namespace App\Providers;

use App\Models\{ContentTemplate, SocialIntegration, Commission, Admin};
use App\Policies\{ContentTemplatePolicy, SocialIntegrationPolicy, CommissionPolicy, AdminPolicy};
use App\Repositories\Eloquent\{ContentTemplateRepository, SocialIntegrationRepository, CommissionRepository, CreatorDashboardRepository, BalanceLedgerRepository, CronRunLogRepository, ReconciliationAlertRepository, AdminRepository, TelegramProductPostRepository, AccountManagerRepository};
use App\Repositories\Interfaces\{ContentTemplateRepositoryInterface, SocialIntegrationRepositoryInterface, CommissionRepositoryInterface, CreatorDashboardRepositoryInterface, BalanceLedgerRepositoryInterface, CronRunLogRepositoryInterface, ReconciliationAlertRepositoryInterface, AdminRepositoryInterface, TelegramProductPostRepositoryInterface, AccountManagerRepositoryInterface};
use Gate;
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
use App\Repositories\Interfaces\CreatorVendorTrackingRepositoryInterface;
use App\Repositories\Eloquent\CreatorVendorTrackingRepository;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Eloquent\SettingRepository;
use App\Services\SettingService;
use App\Repositories\Interfaces\WithdrawRequestRepositoryInterface;
use App\Repositories\Eloquent\WithdrawRequestRepository;
use App\Services\WithdrawRequestService;

use App\Services\SlugService;
use App\Services\TrackingLinkService;
use App\Services\DailyClickService;
use App\Services\Tracking\TrackingUrlGeneratorRegistry;
use App\Services\Tracking\AmazonTrackingUrlGenerator;
use App\Models\Creator;
use App\Observers\CreatorObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');

        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->singleton(SettingService::class);
        $this->app->bind(WithdrawRequestRepositoryInterface::class, WithdrawRequestRepository::class);
        $this->app->singleton(WithdrawRequestService::class);
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
        $this->app->bind(CreatorVendorTrackingRepositoryInterface::class, CreatorVendorTrackingRepository::class);
        $this->app->bind(SocialIntegrationRepositoryInterface::class, SocialIntegrationRepository::class);
        $this->app->bind(ContentTemplateRepositoryInterface::class, ContentTemplateRepository::class);
        $this->app->bind(CommissionRepositoryInterface::class, CommissionRepository::class);
        $this->app->bind(CreatorDashboardRepositoryInterface::class, CreatorDashboardRepository::class);
        $this->app->bind(BalanceLedgerRepositoryInterface::class, BalanceLedgerRepository::class);
        $this->app->bind(CronRunLogRepositoryInterface::class, CronRunLogRepository::class);
        $this->app->bind(ReconciliationAlertRepositoryInterface::class, ReconciliationAlertRepository::class);
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(TelegramProductPostRepositoryInterface::class, TelegramProductPostRepository::class);
        $this->app->bind(AccountManagerRepositoryInterface::class, AccountManagerRepository::class);

        $this->app->singleton(SlugService::class);
        $this->app->singleton(TrackingLinkService::class);
        $this->app->singleton(DailyClickService::class);

        $this->app->singleton(TrackingUrlGeneratorRegistry::class, function ($app) {
            return new TrackingUrlGeneratorRegistry([
                $app->make(AmazonTrackingUrlGenerator::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Creator::observe(CreatorObserver::class);

        Gate::policy(SocialIntegration::class, SocialIntegrationPolicy::class);
        Gate::policy(ContentTemplate::class, ContentTemplatePolicy::class);
        Gate::policy(Commission::class, CommissionPolicy::class);
        Gate::policy(Admin::class, AdminPolicy::class);
    }
}
