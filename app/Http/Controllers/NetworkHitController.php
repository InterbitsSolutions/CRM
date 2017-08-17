<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use App\Models\BucketParams;
use App\Models\User;
use App\DuplicateBuckets;
use App\Models\BucketBrowsers;
use App\Models\BucketFiles;
use App\Models\BucketFolders;
use App\Models\BucketRegions;
use App\Models\BucketShortCodes;
use App\Models\BucketTemplates;
use App\Models\MasterBuckets;
use App\Models\MasterBucketsCounter;
use App\Models\TemplateFiles;
use App\Models\TemplateFolders;
use Aws\DirectoryService\DirectoryServiceClient;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Aws\S3\S3Client;
use App\Classes\S3;
use Mockery\CountValidator\Exception;
use Storage;
use Illuminate\Database\Query\Builder;

use App\Models\NetworkHits;

/*if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJLV6DIJLVNQFOYNA');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

S3::setAuth(awsAccessKey, awsSecretKey);*/

class NetworkHitController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 /*
    public function index()
    {
        
    }
	*/
   

    /*
     * function to get the network hit list
     * created by Swati Mishra
     * created on 03 Aug 17
     * return : array
     */
	public function listNetworkHits(){
        $activeConfigId = $this->getActiveConfig();
        //$networkHits = NetworkHits::get();
		
		/*
		$networkHits = \DB::table('bucket_lead_area')
		->select('bucket_name','customer_ip','city','browser','created_at', \DB::raw('count(*) as hits'))
		->groupBy('city')
		->orderBy('created_at', 'asc')
		->get();
			*/
		$networkHits = NetworkHits::groupBy('city')->orderBy('hits', 'desc')->orderBy('created_at', 'desc')->select('bucket_name','customer_ip','city','browser','created_at', DB::raw('count(*) as hits'))->get();
		
        //echo '<pre>';print_r($networkHits);exit;
        return view('adminsOnly.networkHitList.hitlist', compact('networkHits'));
    } 
	
	
 
    
	
	
	
	
}
