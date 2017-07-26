<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
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
use Aws\S3\S3Client;

use App\Classes\S3;
use Mockery\CountValidator\Exception;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

/*if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJLV6DIJLVNQFOYNA');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

S3::setAuth(awsAccessKey, awsSecretKey);*/

class BucketTestController extends Controller
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
    public function index()
    {
        $storageClient = new StorageClient([
    		'projectId' => '01adsteraa0543',
		]);
		$bucket = $storageClient->bucket('01adsteraa0543');
		
		$adapter = new GoogleStorageAdapter($storageClient, $bucket);
		
		$filesystem = new Filesystem($adapter);

/**
 * The credentials are manually specified by passing in a keyFilePath.
 */

			$storageClient = new StorageClient([
				'projectId' => 'original-folio-171317',
				/*'keyFilePath' => '/crmstaging/my-service.json',*/
			]);
			
		$bucket = $storageClient->bucket('01adsteraa0543');
		
		$adapter = new GoogleStorageAdapter($storageClient, $bucket);
		
		$contents="hello";
		
		$filesystem = new Filesystem($adapter);
				return view('adminsOnly.buckets.testBuckets', compact('contents'));
				
    }

    /*
     * function to get AWS bucket Counter
     * created by BK
     * created on 7th July'17
     */
    public function awsBuckets(){
        //get AWS credentials under CRM
        $configAuth = ConfigAuth::all();
        $awsNetworkArr = array();
        foreach($configAuth as $key => $configDetails){
            try{
                //create object individually
                $awsObject = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'eu-central-1',
                    'credentials' => [
                        'key'    => $configDetails['key'],
                        'secret' => $configDetails['secret']
                    ]
                ]);
                $getContent = $awsObject->listBuckets();
                $totalBuckets = count($getContent['Buckets']);
                //add in array
                $awsNetworkArr[$configDetails['aws_name']]['aws_name'] = $configDetails['aws_name'];
                $awsNetworkArr[$configDetails['aws_name']]['total_buckets'] = $totalBuckets;
            }
            catch(\Exception $exception){
                //catch exception here...
            }
        }
        return array_values($awsNetworkArr);
    }
}
