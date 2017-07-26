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

use Google\Cloud\Storage\StorageClient;

class BucketParamsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /*
    * function to list bucket params
    * created by BK
    * created on 28th June'17
    */
    public function listBucketParams()
    {
        $activeConfigId = $this->getActiveConfig();
        $bucketParams = BucketParams::get();
        return view('adminsOnly.params.viewParams', compact('bucketParams'));
    }

    /*
     * function to manage Bucket params
     * created by BK
     * created on 28th June'17
     */
    public function addBucketParams()
    {
        $activeConfigId = $this->getActiveConfig();
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketParameters = $_POST['bucket_parameters'];
            //check if record exist or not
            $checkBucketParam = BucketParams::where('bucket_region', "=", $bucketRegion)->where('bucket_short_code', "=", $bucketShortCode)->first();
            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucketParam               = new BucketParams();
                $addBucketParam->bucket_region  = $bucketRegion;
                $addBucketParam->bucket_short_code  = $bucketShortCode;
                $addBucketParam->bucket_parameters  = $bucketParameters;
                $addBucketParam->save();
                /*
                 * section to create master bucket
                 */
                $insertedId = $addBucketParam->id;
                $message = "Bucket Parameters has been added successfully!";
                flash($message);
                return Redirect::to("list-bucket-params");
            }else{
                $message = "Bucket already exist in system, please select different inputs!";
                flash($message, 'danger');
                return Redirect::to('add-bucket-params');
            }
        }else{
            $bucketRegions = BucketRegions::get();
            $bucketShortCodes = BucketShortCodes::get();
            return view('adminsOnly.params.addParams', compact('bucketRegions', 'bucketShortCodes'));
        }
    }

    /*
    * function to edit bucket params
    * created by BK
    * created on 28th June'17
    */
    public function editBucketParams($id)
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketParameters = $_POST['bucket_parameters'];
            //add bucket in DB
            $addBucketParam = BucketParams::find($id);
            $addBucketParam->bucket_region  = $bucketRegion;
            $addBucketParam->bucket_short_code  = $bucketShortCode;
            $addBucketParam->bucket_parameters  = $bucketParameters;
            $addBucketParam->save();
            $message = "Bucket Parameters updated successfully!";
            flash($message);
            return Redirect::to("list-bucket-params");
        }
        $bucketParams = BucketParams::findOrFail($id);
        $bucketRegions = BucketRegions::all();
        $bucketShortCodes = BucketShortCodes::all();
        return view('adminsOnly.params.editParams', compact('bucketParams', 'bucketRegions', 'bucketShortCodes'));
    }
    /*
     * function to delete bucket params
     * created by BK
     * created on 28th June'17
     */
    public function deleteBucketParams($bucketID)
    {
        if(!empty($bucketID)){
            $whereArray = array('id'=>$bucketID);
            //delete files from DB
            BucketParams::where($whereArray)->delete();
            flash('Bucket params deleted successfully!');
            return Redirect::to("list-bucket-params");
        }
    }
}
