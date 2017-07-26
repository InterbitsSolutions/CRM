<?php

namespace App\Providers;

use App\Models\MasterBuckets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use App\ConfigAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            //create object of Controller
            $controller =  new Controller();
            $configAuth = ConfigAuth::all();

            //get active config auth id
            $activeConfigId = $controller->getActiveConfig();
            $listMasterBuckets = MasterBuckets::get();
            $totalBuckets = $controller->countBuckets();

            //pass variable data in views
//            $view->with('configAuth', $configAuth)->with('listMasterBuckets', $listMasterBuckets)->with('totalBuckets', $totalBuckets);
            $view->with(compact('configAuth', 'activeConfigId', 'listMasterBuckets', 'totalBuckets'));
            $view;
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
