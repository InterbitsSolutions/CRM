<?php

namespace App\Providers;

use App\Models\MasterBuckets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
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
		
        /*
          check if AWS server active or not, if not, then active a server (BK - 18th Aug'17)
        */
        $getAuthCredentials = ConfigAuth::where('status', "=", 'active')->first();
        if(empty($getAuthCredentials)){
            ConfigAuth::query()->update(['status' => 'inactive']);
            $getLastRecord = ConfigAuth::query()->select('id')->orderBy('id', 'desc')->first();
            if(!empty($getLastRecord))
                ConfigAuth::query()->where('id', "=", $getLastRecord->id)->update(array('status' => 'active'));
        }
        View::composer('*', function ($view) {
            if(Auth::check()) {
                //create object of Controller
                $controller =  new Controller();
                $configAuth = ConfigAuth::all();

                //get active config auth id
                $activeConfigId = $controller->getActiveConfig();
                $listMasterBuckets = MasterBuckets::join('bucket_templates', 'bucket_templates.id', '=','master_buckets.bucket_template')
                    ->select('master_buckets.id','bucket_name','bucket_region','bucket_short_code','bucket_browser','bucket_template','bucket_phone_number','bucket_pid','bucket_analytics_id', 'template_name', 'ringba_code')->get();

                //get Count and current AWS status message
                $totalBuckets = 0;
                $currentAwsStatus = 0;

                //added for session
                if (session::has('awsId')){
                    $awsId                  = session()->get('awsId');
                }else{
                    $awsId = $controller->getActiveConfig();
                }

                //code added by Rajani - ROLES module
                $login_user_display_name = $controller->getName();
                $check_superadmin = $controller->getRole();
                if($check_superadmin == 1){
                    $assigned_modules = $controller->getAllModules();
                }else{
                    $assigned_modules = $controller->getModules();
                }
                $current_url = $controller->getBaseUrl();
                $allowed_urls = $controller->resultUrls();
                if(!empty($allowed_urls)){
                    array_push($allowed_urls,'update-password','activateConfig','primary');
                    if (in_array($current_url, $allowed_urls) || $current_url == ""){
                        $view->with(compact('configAuth', 'activeConfigId', 'listMasterBuckets', 'totalBuckets','assigned_modules','login_user_display_name', 'currentAwsStatus','awsId'));
                    }else{
                        echo "<script>alert('You don\'t have permission to access this page');window.history.back();</script>";exit;
                    }
                }
            }
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
