<?php
namespace App\Http\Controllers;

use App\Models\BucketBrowsers;
use App\Models\BucketFiles;
use App\Models\BucketFolders;
use App\Models\BucketRegions;
use App\Models\BucketShortCodes;
use App\Models\BucketTemplates;
use App\Models\MasterBuckets;
use App\Models\MasterBucketsCounter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Storage;

class MasterBucketsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
    /*****************MASTER BUCKET SECTION******************/
    /*
     * function to list master buckets
     * created by BK
     * created on 6th June'17
     */
    public function listMasterBuckets()
    {
        $totalAwsBuckets = $this->countBuckets();
        $buckets = MasterBuckets::join('bucket_templates', 'bucket_templates.id', '=','master_buckets.bucket_template')
            ->select('master_buckets.id','bucket_name','bucket_region','bucket_short_code','bucket_browser','bucket_template','bucket_phone_number','bucket_pid','bucket_analytics_id', 'template_name', 'ringba_code')->get();
        return view('adminsOnly.masterBuckets.viewMaster', compact('buckets', 'totalAwsBuckets'));
    }

    /*
     * function to add master bucket
     * created by BK
     * created on 5th June'17
     */
    public function addMasterBucket()
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketBrowser = $_POST['bucket_browser'];
            $bucketPhone = $_POST['bucket_phone_number'];
            $bucketPid = $_POST['bucket_pid'];
            $bucketTemplate = $_POST['bucket_template'];
            $bucketAnalyticID = $_POST['bucket_analytics_id'];
            $ringbaCode = $_POST['ringba_code'];

            //get last 4 char of phone number and add in Master Bucket Name
            $trimPhone = str_replace(' ','',$bucketPhone);
            $startPoint =  strlen($trimPhone)- 4;
            $last4Char = substr($trimPhone, $startPoint, 4);

            //create bucket name according to selected fields (ex : afrrpchf0554)
            $bucketName = $bucketShortCode[0].$bucketRegion.$bucketBrowser.$bucketShortCode[1].$last4Char;
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $bucketName)->first();

            //get active config id

            $activeConfigId = $this->getActiveConfig();
            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucket               = new MasterBuckets();
                $addBucket->bucket_name  = $bucketName;
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
                $addBucket->aws_server_id  = $activeConfigId;
                $addBucket->ringba_code  = $ringbaCode;
                $addBucket->save();
                /*
                 * section to create master bucket
                 */
                $insertedId = $addBucket->id;
                $message = "'$bucketName' Bucket has been added successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }else{
                $message = "Bucket with '$bucketName' already exist in system, please select different inputs!";
                flash($message, 'danger');
                return Redirect::to('add-master-bucket');
            }
        }else{
            $activeConfigId = $this->getActiveConfig();
            $bucketRegions = BucketRegions::get();
            $bucketShortCodes = BucketShortCodes::get();
            $bucketBrowsers   = BucketBrowsers::get();
            $bucketTemplates  = BucketTemplates::get();
            return view('adminsOnly.masterBuckets.add', compact('bucketRegions', 'bucketShortCodes', 'bucketBrowsers', 'bucketTemplates'));
        }
    }

    /*
     * function to EDIT master bucket
     * created by BK
     * created on 9th June'17
     */
    public function editMasterBucket($id)
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketBrowser = $_POST['bucket_browser'];
            $bucketPhone = $_POST['bucket_phone_number'];
            $bucketPid = $_POST['bucket_pid'];
            $bucketTemplate = $_POST['bucket_template'];
            $bucketAnalyticID = $_POST['bucket_analytics_id'];
            $ringbaCode = $_POST['ringba_code'];

            //get last 4 char of phone number and add in Master Bucket Name
            $trimPhone = str_replace(' ','',$bucketPhone);
            $startPoint =  strlen($trimPhone)- 4;
            $last4Char = substr($trimPhone, $startPoint, 4);

            //create bucket name according to selected fields (ex : afrrpchf0554)
            $bucketName = $bucketShortCode[0].$bucketRegion.$bucketBrowser.$bucketShortCode[1].$last4Char;

            //get active config id
            $activeConfigId = $this->getActiveConfig();
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $bucketName)->where('aws_server_id', "=", $activeConfigId)->first();

            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucket               = MasterBuckets::find($id);
                $addBucket->bucket_name  = $bucketName;
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
                $addBucket->ringba_code  = $ringbaCode;
                $addBucket->save();
                /*
                 * section to create master bucket
                 */
                $message = "'$bucketName' Bucket updated successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }else{
                //add bucket in DB
                $addBucket               = MasterBuckets::find($id);
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
				 $addBucket->ringba_code  = $ringbaCode;
                $addBucket->save();
                $message = "'$bucketName' Bucket updated successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }
        }else{
            $currentBucketDetails = MasterBuckets::findOrFail($id);
            $activeConfigId = $this->getActiveConfig();
            $bucketRegions = BucketRegions::get();
            $bucketShortCodes = BucketShortCodes::get();
            $bucketBrowsers   = BucketBrowsers::get();
            $bucketTemplates  = BucketTemplates::get();
            return view('adminsOnly.masterBuckets.edit', compact('currentBucketDetails','bucketRegions', 'bucketShortCodes', 'bucketBrowsers', 'bucketTemplates'));
        }
    }
    /*
     * function to delete master bucket
     * created by BK
     * created on 7th June'17
     */
    public function deleteMasterBucket($bucketID)
    {
        if(!empty($bucketID)){
//            unlink(public_path('bucket_data').DIRECTORY_SEPARATOR.$masterBucketID);
            $whereArray = array('bucket_id'=>$bucketID);
            //delete files from DB
            BucketFiles::where($whereArray)->delete();
            //delete folder entries from from DB
            BucketFolders::where($whereArray)->delete();

            $whereArray = array('id'=>$bucketID);
            MasterBuckets::where($whereArray)->delete();
            flash('Bucket deleted successfully!');
            return redirect('/list-master-buckets');
        }
    }

    /*
    * function to copy master bucket from one to other AWS server
    * created by BK
    * created on 30th June'17
    */
    public function copyMasterBucket(Request $request){
        if(!empty($_POST['aws_server_id']) && !empty($_POST['master_bucket_id'])){
            $masterBucketID = $request->input('master_bucket_id');
            $awsServerID = $request->input('aws_server_id');
            $awsServerName = $request->input('aws_server_name');
            //existing master bucket record
            $existRecord = MasterBuckets::find($masterBucketID);
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $existRecord->bucket_name)->where('aws_server_id', "=", $awsServerID)->first();

            if(empty($checkBucketExist)){
                $new = $existRecord->replicate();
                $new->aws_server_id = $awsServerID;
                $new->save();
                //return with flash message
                $message = "Master bucket successfully copy to $awsServerName!";
                flash($message);
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                return json_encode($return);
            }else{
                //return with flash message
                $message = "Master bucket already exist in AWS: $awsServerName!";
                $return = array(
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in your parameters, please check and try again later!";
            //return response
            $return = array(
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /*
     * function to manage second step of master bucket - UPLOAD FILES
     * created by BK
     * created on 5th June'17
     */
    public function uploadMasterFiles($bucketId, $folderIN= null)
    {
        if(!empty($folderIN)){
            $getCurrentFolderName = explode('/',$folderIN);
            $folderName = end($getCurrentFolderName);

            //get folder name
            $folderNameDetails = BucketFolders::where('bucket_id', "=", $bucketId)->where('folder_name', '=', $folderName)->first();
            $folderID = $folderNameDetails['id'];
            $parentFolder = $folderNameDetails['parent_folder'];

            //if in folder, then get files of folder
            $bucketFiles = BucketFiles::where('bucket_id', "=", $bucketId)->where('folder_id', '=', $folderID)->get();
            $bucketFolders = BucketFolders::where('bucket_id', "=", $bucketId)->where('parent_folder', '!=', 0)->where('parent_folder', '=', $folderID)->where('folder_name', '!=', $folderName)->get();
        }else{
            $folderID = '';
            $folderName = '';
            //if at root, then show files and folder for the same
            $bucketFiles = BucketFiles::where('bucket_id', "=", $bucketId)->where('folder_id', '=', 0)->get();
            $bucketFolders = BucketFolders::where('bucket_id', "=", $bucketId)->where('parent_folder', '=', 0)->get();
        }
        return view('adminsOnly.masterBuckets.upload', compact('bucketId', 'folderIN', 'folderID', 'folderName', 'bucketFiles', 'bucketFolders'));
    }

}
