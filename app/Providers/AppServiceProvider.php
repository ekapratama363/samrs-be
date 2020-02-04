<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // custom migration for max string
        Schema::defaultStringLength(191);
        // custom unique validation insensitive case
        Validator::extend('iunique', function ($attribute, $value, $parameters, $validator) {
            $query = \DB::table($parameters[0]);
            $column = $query->getGrammar()->wrap($parameters[1]);
            return ! $query->whereRaw("lower({$column}) = lower(?)", [$value])->count();
        });

        // record ip address to activity log
        Activity::saving(function (Activity $activity) {
            $activity->properties = $activity->properties->put('ip', request()->ip());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
