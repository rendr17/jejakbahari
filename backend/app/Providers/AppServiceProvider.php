<?php

namespace App\Providers;

use App\Models\DataSource;
use App\Models\Operator;
use App\Models\RegistryEvidence;
use App\Models\Vessel;
use App\Policies\DataSourcePolicy;
use App\Policies\OperatorPolicy;
use App\Policies\RegistryEvidencePolicy;
use App\Policies\VesselPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Operator::class, OperatorPolicy::class);
        Gate::policy(Vessel::class, VesselPolicy::class);
        Gate::policy(DataSource::class, DataSourcePolicy::class);
        Gate::policy(RegistryEvidence::class, RegistryEvidencePolicy::class);
    }
}
