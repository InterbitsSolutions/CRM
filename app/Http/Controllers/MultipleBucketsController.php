<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use App\Models\BucketParams;
use App\Models\User;
use App\DuplicateBuckets;
use App\Models\BucketRegions;
use App\Models\BucketTemplates;
use App\Models\MasterBuckets;
use App\Models\MasterBucketsCounter;
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


class MultipleBucketsController extends Controller
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
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit','1024M');
        $activeConfigId = $this->getActiveConfig();
        if(!empty($_POST['aws_networks'])){
            $bucketArray = array();
            $selectedNetworks = $_POST['aws_networks'];
            foreach($_POST['aws_networks'] as $key => $recordID){
                //get aws server Details
                $bucketAuthCredentials = $this->getCredentials($recordID);
                $bucketKey = $bucketAuthCredentials['key'];
                $bucketSecret = $bucketAuthCredentials['secret'];
                $awsServerName = (!empty($bucketAuthCredentials['aws_name'])) ? $bucketAuthCredentials['aws_name'] : '-';

                //create s3 client
                $s3client = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'eu-central-1',
                    'credentials' => [
                        'key'    => $bucketKey,
                        'secret' => $bucketSecret
                    ]
                ]);
                //catch exception if found..
                try{
                    $contents = $s3client->listBuckets();
                    $totalBuckets = count($contents['Buckets']);
                    //add auth id and network in buckets array
                    foreach ($contents['Buckets'] as $key => $bucketDetails){
                        if(!empty($bucketDetails['Name'])){
                            $bucketName = $bucketDetails['Name'];
                            try{
                                $location = $s3client->getBucketLocation(array(
                                    'Bucket' => $bucketDetails['Name']
                                ));
                                $contents['Buckets'][$key]['LocationConstraint'] = $location['LocationConstraint'];
                                $contents['Buckets'][$key]['bucketConfigAuth'] = $recordID;
                                $contents['Buckets'][$key]['bucketConfigName'] = $awsServerName;
                                $contents['Buckets'][$key]['totalNetworkBuckets`'] = $totalBuckets;
                            }
                            catch(\Exception $exception){
                            }
                        }
                    }
                    $bucketArray = array_merge($bucketArray,$contents['Buckets']);
                }
                catch (\Aws\S3\Exception\S3Exception $e) {
                    //exception found
                }
            }
            $contents['Buckets'] = $bucketArray;
        }else{
            //get aws server Details
            $bucketAuthCredentials = $this->getAuthCredentials();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $awsServerName = (!empty($bucketAuthCredentials['aws_name'])) ? $bucketAuthCredentials['aws_name'] : '-';

            //create s3 client
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => 'eu-central-1',
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            $contents = $s3client->listBuckets();
            //add auth id and network in buckets array
            foreach ($contents['Buckets'] as $key => $bucketDetails){
                $totalBuckets = count($contents['Buckets']);
                if(!empty($bucketDetails['Name'])){
                    $bucketName = $bucketDetails['Name'];
                    try{
                        $location = $s3client->getBucketLocation(array(
                            'Bucket' => $bucketDetails['Name']
                        ));
                        $contents['Buckets'][$key]['LocationConstraint'] = $location['LocationConstraint'];
                        $contents['Buckets'][$key]['bucketConfigAuth'] = $activeConfigId;
                        $contents['Buckets'][$key]['bucketConfigName'] = $awsServerName;
                        $contents['Buckets'][$key]['totalNetworkBuckets`'] = $totalBuckets;
                    }
                    catch(\Exception $exception){
                    }
                }
            }
            //selected networks
            $selectedNetworks[] = $activeConfigId;
        }
        // Using operation methods creates command implicitly.
        foreach($contents['Buckets'] as $content){
            if (preg_match('/www/',$content['Name'])){
                //get bucket first string
                $firstString = substr($content['Name'], 0, strcspn($content['Name'], '1234567890'));
                $replaceCommonString = str_replace(array($firstString,'.com'), '' , $content['Name']);

                //replace first find string from string
                $getUniqueNumber = $this->getNumericVal($replaceCommonString);
                if(!empty($getUniqueNumber)){
                    $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
                    //check if duplicate bucket record exist or not
                    $checkBucketExist = DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->first();
                    if(empty($checkBucketExist)){
                        //add entry in Duplicate bucket
                        $addDuplicateBucket               = new DuplicateBuckets();
                        $addDuplicateBucket->bucket_name  = $content['Name'];
                        $addDuplicateBucket->bucket_code  = $finalString;
                        $addDuplicateBucket->duplicate_bucket_counter  = $getUniqueNumber;
                        $addDuplicateBucket->aws_server_id  = $activeConfigId;
                        $addDuplicateBucket->save();
                    }else{
                        DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $getUniqueNumber]);
                    }
                }
            }
        }


        //create master bucket PID data
        $masterBuckets = MasterBuckets::all();
        $bucketPidArr = array();
        foreach($masterBuckets  as $key => $masterData){
            $bucketPidArr[$masterData['bucket_name']]['id'] = $masterData['id'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_name'] = $masterData['bucket_name'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_pid'] = $masterData['bucket_pid'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_phone_number'] = str_replace(' ', '', $masterData['bucket_phone_number']);
            $bucketPidArr[$masterData['bucket_name']]['ringba_code'] = str_replace(' ', '', $masterData['ringba_code']);
            $bucketPidArr[$masterData['bucket_name']]['bucket_region'] = $masterData['bucket_region'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_short_code'] = $masterData['bucket_short_code'];
            //get bucket params
            $getBucketParams = BucketParams::where('bucket_region', "=", $masterData['bucket_region'])->where('bucket_short_code', '=', $masterData['bucket_short_code'])->first();
            $bucketPidArr[$masterData['bucket_name']]['bucket_parameters'] = (!empty($getBucketParams)) ? $getBucketParams['bucket_parameters'] : '';
        }

        // bucket PARAMS and create master bucket PID data
        $getBucketParams = BucketParams::get();
        $bucketParamArr = array();
        foreach($getBucketParams  as $key => $paramData){
            $key = $paramData['bucket_short_code'][0].$paramData['bucket_region'].$paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_region'] = $paramData['bucket_region'];
            $bucketParamArr[$key]['bucket_short_code'] = $paramData['bucket_short_code'];
            $bucketParamArr[$key]['startString'] = $paramData['bucket_short_code'][0].$paramData['bucket_region'];
            $bucketParamArr[$key]['endString'] = $paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_parameters'] = $paramData['bucket_parameters'];
        }

        //get templates from DB that not to be shown in Buckets
        $templates = DB::table('bucket_templates')->select( DB::raw('group_concat(aws_name) AS template_names'))->first();
        $templateArr = array_filter(explode(',', $templates->template_names));
        //list of all aws server
        $status        =  "Inactive";
        $allAwsServer  =  ConfigAuth::where('status', "=", $status)->get();
        return view('adminsOnly.multipleBuckets.index', compact('contents', 's3client', 'masterBuckets', 'bucketPidArr', 'templateArr', 'bucketParamArr','allAwsServer', 'selectedNetworks'));
    }

    /*
     * function to make bucket duplicate
     * created by BK
     * created on 2nd June'17
     */
    public function duplicator(){
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit','1024M');

        if(!empty($_POST['duplicate_for']) && !empty($_POST['duplicate_counter']) && !empty($_POST['duplicate_auth_id'])) {
            //get bucket details
            $bucket = $_POST['duplicate_for'];
            $bucketCounter = $_POST['duplicate_counter'];
            $bucketRegion = $_POST['duplicate_for_region'];
            $authConfigID = $_POST['duplicate_auth_id'];

            //get bucket Regions
//            $regionArr = $this->getRegions();
            //get bucket first string
            $firstString = substr($bucket, 0, strcspn($bucket, '1234567890'));
            $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucket);

            //replace first find string from string
            $getUniqueNumber = $this->getNumericVal($replaceCommonString);
            $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);

//            $bucketRegion = 'fr';
//            foreach ($regionArr as $regionKey => $regionCodeVal) {
//                if (stristr($replaceCommonString, $regionKey) !== FALSE) {
//                    $bucketRegion = $regionKey;
//                }
//            }
            //get region code
            $regionCode = (!empty($bucketRegion)) ? $bucketRegion : "eu-central-1";
            if(empty($regionCode)){
                //return response
                $return = array(
                    'type' => 'error',
                    'message' => "Region code cannot be empty for $bucketRegion.",
                );
                return json_encode($return);
            }

            //create object for "S3Client"
//            $bucketAuthCredentials = $this->getAuthCredentials();
            $bucketAuthCredentials = $this->getCredentials($authConfigID);
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $regionCode,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            // Using operation methods creates command implicitly.
            $bucketArray = $s3client->listBuckets();

            $bucketSuccessResponse = array();
            for($counter = 1; $counter <= $bucketCounter; $counter++ ){
                $activeConfigId = $this->getActiveConfig();
                $checkBucketExist = DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->first();

                $duplicateExist = false;
                if(!empty($checkBucketExist)){
                    $checkBucketExist['duplicate_bucket_counter'] =  $checkBucketExist['duplicate_bucket_counter']+1;
                    $newCounter = ($checkBucketExist['duplicate_bucket_counter']<10) ? '0'.$checkBucketExist['duplicate_bucket_counter'] : $checkBucketExist['duplicate_bucket_counter'];
                    $duplicateExist = true;
                }
                else{
                    //get array of matches string in Bucket name
                    $matchCases =  array();
                    foreach($bucketArray['Buckets'] as $bucketDetail) {
                        if (strpos($bucketDetail['Name'], $finalString) !== false) {
                            $matchCases[] = $bucketDetail;
                        }
                    }
                    $getLastRecord = end($matchCases);

                    $getLastEntry = str_replace(array($firstString,'.com', $finalString), '' , $getLastRecord['Name']);
                    $incrementRecord = $getLastEntry+1;
                    $newCounter = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
                }
                //create next new bucket name
                $newBucketName = $firstString.$newCounter.$finalString.'.com';
                //create string policy for Bucket
                $stringPolicy ='{
                    "Version": "2012-10-17",
                    "Statement": [
                        {
                            "Sid": "Allow Public Access to All Objects",
                            "Effect": "Allow",
                            "Principal": "*",
                            "Action": "s3:GetObject",
                            "Resource": "arn:aws:s3:::'.$newBucketName.'/*"
                        }
                    ]
                }';
                //get list of all buckets and check if bucket name already exist
                $existName = false;
                $contents = $s3client->listBuckets();
                foreach ($contents['Buckets'] as $bucketDetails) {
                    if ($newBucketName == $bucketDetails['Name']) {
                        $existName = true;
                    }
                }
                //if name already exist, then return error message
                if ($existName) {
                    $message = "'$newBucketName' bucket already exist, please try with some other name!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
                else {
                    //check index.html file for existing bucket
                    $existIndex = false;
                    $existingBucket = $s3client->listObjects(array('Bucket' => $bucket));
                    foreach ($existingBucket['Contents'] as $existFiles) {
                        if ($existFiles['Key'] == 'index.html') {
                            $existIndex = true;
                        } else {
                            $existIndex = false;
                        }
                    }
                    //if index file exist, then create bucket
                    if ($existIndex) {
                        try{
                            //trigger exception in a "try" block
                            $result3 = $s3client->createBucket([
                                'Bucket' => $newBucketName,
                            ]);
                            $stp = $s3client->listObjects(array('Bucket' => $bucket));
                            foreach ($stp['Contents'] as $object) {
                                $s3client->copyObject(array(
                                    'Bucket' => $newBucketName,
                                    'Key' => $object['Key'],
                                    'CopySource' => $bucket . '/' . $object['Key']
                                ));
                            }
                            $arg = array(
                                'Bucket' => $newBucketName,
                                'WebsiteConfiguration' => array(
                                    'ErrorDocument' => array('Key' => 'error.html',),
                                    'IndexDocument' => array('Suffix' => 'index.html',),
                                ),
                            );
                            $result2 = $s3client->putBucketWebsite($arg);
                            $result3 = $s3client->putBucketPolicy([
                                'Bucket' => $newBucketName,
                                'Policy' => $stringPolicy,
                            ]);

                            //if already exist, update the counter, else add new entry
                            if($duplicateExist){
                                DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $newCounter]);
                            }else{
                                //add entry in Duplicate bucket
                                $addDuplicateBucket               = new DuplicateBuckets();
                                $addDuplicateBucket->bucket_name  = $newBucketName;
                                $addDuplicateBucket->bucket_code  = $finalString;
                                $addDuplicateBucket->aws_server_id  = $activeConfigId;
                                $addDuplicateBucket->duplicate_bucket_counter  = $newCounter;
                                $addDuplicateBucket->save();
                            }
                            //get location for new bucket url
                            $location = $s3client->getBucketLocation(array(
                                'Bucket' => $newBucketName
                            ));
                            $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                            $bucketSuccessResponse[] = "$newBucketUrl";
                            //response in case of success if counter match!
                            if($counter==$bucketCounter){
                                $finalMessage =  implode(' , ', $bucketSuccessResponse).' bucket successfully created!';
                                flash($finalMessage);
                                //return response
                                $return = array(
                                    'type' => 'success',
                                    'message' => $bucketSuccessResponse,
                                );
                                return json_encode($return);
                            }
                        }
                        catch(\Exception $exception){
                            $xmlResponse = $exception->getAwsErrorCode();
                            if($xmlResponse=="BucketAlreadyExists"){
                                $message = "Bucket already exists. Please change the URL.";
                            }else{
                                $message = $xmlResponse;
                            }
                            $return = array(
                                'value' => '2',
                                'type' => 'error',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }
                    } else {
                        $message = "Index.html file must be in your existing bucket, please add and try again later!";
                        //return response
                        $return = array(
                            'value' => '100',
                            'type' => 'error',
                            'message' => $message,
                        );
                        return json_encode($return);
                    }
                }
            }
        }
        else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /*
     * function to delete bucket
     * created by BK
     * created on 2nd June'17
     */
    public function deleteBucket(){
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit','1024M');

        if(!empty($_POST)) {
            $bucketName = $_POST['bucket_name'];
            $authConfigID = $_POST['auth_config_id'];

            if(!empty($bucketName) && !empty($authConfigID)){
                try {
                    $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                    //replace string, get unique number and get final string of the BUCKET
                    $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);
                    //get bucket Regions
                    $regionArr = $this->getRegions();
                    $bucketRegion = 'fr';
                    foreach ($regionArr as $regionKey => $regionCodeVal) {
                        if (stristr($replaceCommonString, $regionKey) !== FALSE) {
                            $bucketRegion = $regionKey;
                        }
                    }
                    //get region code
                    $regionCode = (!empty($bucketRegion)) ? $regionArr[$bucketRegion] : "eu-central-1";
                    if(empty($regionCode)){
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => "Region code cannot be empty for $bucketRegion.",
                        );
                        return json_encode($return);
                    }
                    //create object for "S3Client"
//                    $bucketAuthCredentials = $this->getAuthCredentials();
                    $bucketAuthCredentials = $this->getCredentials($authConfigID);
                    $bucketKey = $bucketAuthCredentials['key'];
                    $bucketSecret = $bucketAuthCredentials['secret'];

                    $s3client = new S3Client([
                        'version'     => 'latest',
                        'region'      => $regionCode,
                        'credentials' => [
                            'key'    => $bucketKey,
                            'secret' => $bucketSecret
                        ]
                    ]);
                    $cont = $s3client->getIterator('ListObjects', array('Bucket' => $bucketName));
                    foreach ($cont as $fileDetails){
                        $fileName = $fileDetails['Key'];
                        $result = $s3client->deleteObject(array(
                            'Bucket' => $bucketName,
                            'Key'    => $fileName
                        ));
                    }
                    $s3client->deleteBucket(array(
                        'Bucket' => $bucketName
                    ));

                    $message = "Success ";
                    //return response
                    $return = array(
                        'type' => 'success',
                        'message' => $message,
                    );
                    flash("$bucketName deleted successfully!");
                    return json_encode($return);
                }
                catch(Exception $e){
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $e->getMessage(),
                    );
                    return json_encode($return);
                }
            }else{
                $message = "Bucket name cannot be empty, please check!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }
    /*
     * function to delete bucket in BULK
     * created by BK
     * created on 2nd June'17
     */
    public function deleteMultipleBuckets(){
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit','1024M');

        if(!empty($_POST)) {
            //get buckets
            $buckets = $_POST['bucket_name'];
            if(!empty($buckets)){
                foreach($buckets as $key => $bucketDetails){
                    //get Auth ID and Bucket name
                    $explodeDetails =  explode('_',$bucketDetails);
                    $bucketName = $explodeDetails[0];
                    $bucketAuth = $explodeDetails[1];

                    $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                    //replace string, get unique number and get final string of the BUCKET
                    $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);

                    //get bucket Regions
                    $regionArr = $this->getRegions();
                    $bucketRegion = 'fr';
                    foreach ($regionArr as $regionKey => $regionCodeVal) {
                        if (stristr($replaceCommonString, $regionKey) !== FALSE) {
                            $bucketRegion = $regionKey;
                        }
                    }
                    //get region code
                    $regionCode = (!empty($bucketRegion)) ? $regionArr[$bucketRegion] : "eu-central-1";
                    if(empty($regionCode)){
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => "Region code cannot be empty for $bucketRegion.",
                        );
                        return json_encode($return);
                    }


                    //create object for "S3Client"
//                    $bucketAuthCredentials = $this->getAuthCredentials();
                    $bucketAuthCredentials = $this->getCredentials($bucketAuth);
                    $bucketKey = $bucketAuthCredentials['key'];
                    $bucketSecret = $bucketAuthCredentials['secret'];

                    $s3client = new S3Client([
                        'version'     => 'latest',
                        'region'      => $regionCode,
                        'credentials' => [
                            'key'    => $bucketKey,
                            'secret' => $bucketSecret
                        ]
                    ]);
                    $cont = $s3client->getIterator('ListObjects', array('Bucket' => $bucketName));
                    foreach ($cont as $fileDetails){
                        $fileName = $fileDetails['Key'];
                        $result = $s3client->deleteObject(array(
                            'Bucket' => $bucketName,
                            'Key'    => $fileName
                        ));
                    }
                    $s3client->deleteBucket(array(
                        'Bucket' => $bucketName
                    ));
                }
                $message = "Success ";
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                $bucketNames = implode(' , ',$_POST['bucket_name']);
                flash("$bucketNames bucket deleted successfully!");
                return json_encode($return);
            }
            else{
                $message = "Bucket name cannot be empty, please check!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    public function getNumericVal ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return (!empty($matches[0][0])) ? $matches[0][0] : '';
    }

    public function duplicateListBuckets()
    {
        $buckets = DuplicateBuckets::all();
        return view('adminsOnly.buckets.view', compact('buckets'));
    }

    /*
    * function to create bucket from master bucket
    * created by BK
    * created on 8th June'17
    */
    public function createChildBucket()
    {
        if(!empty($_POST)){
            //get master bucket details
            $masterBucketID = $_POST['master_bucket'];
            $masterBucketDetails = MasterBuckets::find($masterBucketID);

            //master bucket var
            $masterBucketName = $masterBucketDetails['bucket_name'];
            $bucketRegion = $masterBucketDetails['bucket_region'];
            $bucketShortCode = $masterBucketDetails['bucket_short_code'];
            $bucketBrowser = $masterBucketDetails['bucket_browser'];
            $bucketPhoneNumber = $masterBucketDetails['bucket_phone_number'];
            $bucketPID = $masterBucketDetails['bucket_pid'];

            //get region code from - required
            $regionCode = BucketRegions::where('region_value', "=", $bucketRegion)->first();
            $regionCode = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            if(empty($regionCode)){
                //return response
                $return = array(
                    'type' => 'error',
                    'message' => "Region code cannot be empty for $bucketRegion.",
                );
                return json_encode($return);
            }

            //get bucket template details
            $bucketTemplate = $masterBucketDetails['bucket_template'];
            $templateDetails = BucketTemplates::find($bucketTemplate);
            $awsName = $templateDetails['aws_name'];

            //get counter and add on 1
            $childBucketCounter = $masterBucketDetails['child_bucket_counter'];
            $incrementCounter = $childBucketCounter+1;
            $newCounter = ($incrementCounter<10) ? '0'.$incrementCounter : $incrementCounter;

            //add child bucket detail
//            $childBucketName = $newCounter.$masterBucketName;
//            $childBucketLink = "www.support.microsoft$childBucketName.com";

            //sete params tp create bucket
            $bucketParams = array();
            $bucketParams['duplicate_for'] = $awsName;
            $bucketParams['region_code'] = $regionCode;
            $bucketParams['bucket_counter'] = $newCounter;
            $bucketParams['bucket_basic_name'] = $masterBucketName;
            $bucketParams['bucket_phone_number'] = $bucketPhoneNumber;

            // $createBucketResponse = json_decode($this->duplicatorMaster($bucketParams));
            $createBucketResponse = json_decode($this->duplicateUsingMasterTemplate($bucketParams));

            if($createBucketResponse->type=='success'){
                $updatedCounter = $createBucketResponse->bucket_updated_counter;
                $childBucketName = $createBucketResponse->bucket_url;
                $serverName         = $createBucketResponse->bucket_created_server_name;
                //update counter in master bucket table
                MasterBuckets::where('id', $masterBucketID)->update(['child_bucket_counter' => $updatedCounter]);

                $activeConfigId = $this->getActiveConfig();
                DuplicateBuckets::where('bucket_code', $masterBucketName)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $updatedCounter]);

                $message = "$childBucketName bucket has been added successfully on Sever : $serverName !";
                flash($message);
                //return response
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                return json_encode($return);
            }else{
                return json_encode($createBucketResponse);
            }
        }
    }

    /*
    * function to make bucket duplicate
    * created by BK
    * created on 2nd June'17
    */

    public function duplicatorMaster($bucketParams){
        if(!empty($bucketParams)) {
            //bucket params
            $bucket = $bucketParams['duplicate_for'];
            $bucketCounter = $bucketParams['bucket_counter'];
            $bucketRegionCode = $bucketParams['region_code'];
            $bucketBasicName = $bucketParams['bucket_basic_name'];

            //create object for "S3Client"
            $bucketAuthCredentials = $this->getAuthCredentials();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $bucketRegionCode,

                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //get list of all buckets and check if bucket name already exist
            $existName = false;
            $contents = $s3client->listBuckets();

            //get array of matches string in Bucket name
            $matchCases =  array();
            foreach($contents['Buckets'] as $bucketDetail) {
                if (strpos($bucketDetail['Name'], $bucketBasicName) !== false) {
                    $matchCases[] = $bucketDetail;
                }
            }

            //get last bucket counter
            if(!empty($matchCases)){
                $getLastRecord = end($matchCases);
                $firstString = substr($getLastRecord['Name'], 0, strcspn($getLastRecord['Name'], '1234567890'));
                $getLastEntry = str_replace(array($firstString,'.com', $bucketBasicName), '' , $getLastRecord['Name']);
                $incrementRecord = $getLastEntry+1;
                $newCounter = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
            }else{
                $firstString = 'www.support.microsoft';
                $bucketCounter = $this->getConfigCounter();
                $newCounter = ($bucketCounter<10) ? '0'.$bucketCounter : $bucketCounter;
            }

            //create final bucket name
            $childBucketName = $newCounter.$bucketBasicName;
            $newBucketName = "$firstString$childBucketName.com";

            $stringPolicy ='{
                "Version": "2012-10-17",
                "Statement": [
                    {
                        "Sid": "Allow Public Access to All Objects",
                        "Effect": "Allow",
                        "Principal": "*",
                        "Action": "s3:GetObject",
                        "Resource": "arn:aws:s3:::'.$newBucketName.'/*"
                    }
                ]
            }';

            foreach ($contents['Buckets'] as $bucketDetails) {
                if ($newBucketName == $bucketDetails['Name']) {
                    $existName = true;
                }
            }
            //if name already exist, then return error message
            if ($existName) {
                $message = "'$newBucketName' bucket already exist, please try with some other name!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            } else {
                //check index.html file for existing bucket
                $existIndex = false;
                $existingBucket = $s3client->listObjects(array('Bucket' => $bucket));
                foreach ($existingBucket['Contents'] as $existFiles) {
                    if ($existFiles['Key'] == 'index.html') {
                        $existIndex = true;
                    } else {
                        $existIndex = false;
                    }
                }
                //if index file exist, then create bucket
                if ($existIndex) {

                    $result3 = $s3client->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    $stp = $s3client->listObjects(array('Bucket' => $bucket));
                    foreach ($stp['Contents'] as $object) {
                        $s3client->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );
                    $result2 = $s3client->putBucketWebsite($arg);
                    $result3 = $s3client->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    $location = $s3client->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    //return response
                    $return = array(
                        'type' => 'success',
                        'bucket_url' => $newBucketUrl,
                        'bucket_updated_counter' => $newCounter,
                    );
                    return json_encode($return);

                } else {
                    $message = "Index.html file must be in your selected Template of Master bucket, please add and try again later!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /*
     * function to get the region array
     * created by BK
     * created on 27th June
     * return : array
     */
    public function getRegions(){
        $bucketRegions = BucketRegions::all();
        $regionArr = array();
        foreach($bucketRegions as $regions){
            $regionArr[$regions->region_value] = $regions->region_code;
        }
        return $regionArr;
    }

    /*
    * function to make bucket duplicate
    * created by NK
    * created on 30 June'17
    */
    public function duplicateToAws()
    {
        $duplciateFrom          = Input::get('duplicate_for');
        $newBucketName          = Input::get('new_bucket_name');
        $region                 = Input::get('duplicateToAwsRegion');
        $status                 = "Active";
        $awsServerActive        = ConfigAuth::where('status', "=", $status)->first();
        $activeServerKey        = $awsServerActive['key'];
        $actvieServerSecretKey  = $awsServerActive['secret'];
        $copyToServerId         = Input::get('aws_server_id');
        $allAwsServer           = ConfigAuth::where('id', "=", $copyToServerId)->first();
        $toServerKey            = $allAwsServer['key'];
        $toServerSecretKey      = $allAwsServer['secret'];
        $toServerName           = $allAwsServer['aws_name'];
        $bucket 		        = $duplciateFrom;
        //create object for "S3Client"
        $s3clientActive               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $activeServerKey,
                'secret' => $actvieServerSecretKey
            ]
        ]);
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);
        if($newBucketName=="")
        {
            $newBucketName = $duplciateFrom;
        }
        //create string policy for Bucket
        $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';

        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3clientToMove->listBuckets();
        foreach ($contents['Buckets'] as $bucketDetails) {
            if ($newBucketName == $bucketDetails['Name']) {
                $existName = true;
            }
        }

        //if name already exist, then return error message
        if ($existName) {
            $message = "'$newBucketName' bucket already exist, please try with some other name!";
            //return response
            $return = array(
                'value' => '2',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
        else {
            //check index.html file for existing bucket
            $existIndex = false;
            $existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
            foreach ($existingBucket['Contents'] as $existFiles) {
                if ($existFiles['Key'] == 'index.html') {
                    $existIndex = true;
                } else {
                    $existIndex = false;
                }
            }
            //if index file exist, then create bucket
            if($existIndex)
            {
                try{
                    //create instance of NEW server, where we have to move/copy
                    $result3 = $s3clientToMove->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    //list the current bucket from active AWS server, from where we have to move/copy
                    $stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to

                    foreach ($stp['Contents'] as $object) {
                        //create instance of NEW server, where we have to move/copy
                        $s3clientToMove->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );

                    //create instance of NEW server, where we have to move/copy
                    $result2 = $s3clientToMove->putBucketWebsite($arg);
                    $result3 = $s3clientToMove->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    //create instance of NEW server, where we have to move/copy
                    $location = $s3clientToMove->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    //response in case of success if counter match!
                    $finalMessage =  $newBucketUrl.' bucket successfully created on new server'.$toServerName;
                    flash($finalMessage);
                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'message' => $finalMessage,
                        'b_url' => $newBucketUrl,
                        'b_name'=>$newBucketName,
                    );
                    return json_encode($return);
                }
                catch(\Exception $exception){

                    $xmlResposne = $exception->getAwsErrorCode();
                    if($xmlResposne=="BucketAlreadyExists")
                    {
                        $message = "Bucket already exists. Please change the URL.";
                    }
                    else
                    {
                        $message = $xmlResposne;
                    }
                    $return = array(
                        'value' => '2',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);

                }

            }
        }
    }

    /*
	* function to make bucket duplicate
	* created by NK
	* created on 11 July'17
    * $s3clientActive means from where we copy the master template robert
    * $s3clientToMove is the object of active server
	*/
    public function duplicateUsingMasterTemplate($bucketParams)
    {
        $duplciateFrom          = $bucketParams['duplicate_for'];
        $region                 = $bucketParams['region_code'];
        $bucket_counter         = $bucketParams['bucket_counter'];
        $bucketBasicName        = $bucketParams['bucket_basic_name'];
        $bucketPhoneNumber      = $bucketParams['bucket_phone_number'];
        $primary                = "yes";
        $status                 = "active";
        //$awsServerActive       = ConfigAuth::where('status', "=", $status)->first();
        $awsServerActive        = ConfigAuth::where('primary_network', "=", $primary)->first();
        $activeServerKey        = $awsServerActive['key'];
        $actvieServerSecretKey  = $awsServerActive['secret'];
        $allAwsServer           = ConfigAuth::where('status', "=", $status)->first();
        $toServerKey            = $allAwsServer['key'];
        $toServerSecretKey      = $allAwsServer['secret'];
        $toServerName           = $allAwsServer['aws_name'];
        $bucket 		        = $duplciateFrom;
        //create object for "S3Client"
        $s3clientActive               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $activeServerKey,
                'secret' => $actvieServerSecretKey
            ]
        ]);
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);

        /* code to final the bucekt name */
        $contents = $s3clientToMove->listBuckets();
        //get array of matches string in Bucket name
        $matchCases =  array();
        foreach($contents['Buckets'] as $bucketDetail) {
            if (strpos($bucketDetail['Name'], $bucketBasicName) !== false) {
                $matchCases[] = $bucketDetail;
            }
        }
        //get last bucket counter
        if(!empty($matchCases)){
            $getLastRecord      = end($matchCases);
            $firstString        = substr($getLastRecord['Name'], 0, strcspn($getLastRecord['Name'], '1234567890'));
            $getLastEntry       = str_replace(array($firstString,'.com', $bucketBasicName), '' , $getLastRecord['Name']);
            $incrementRecord    = $getLastEntry+1;
            $newCounter         = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
        }else{
            $firstString        = 'www.support.microsoft';
            $bucketCounter      = $this->getConfigCounter();
            $newCounter         = ($bucketCounter<10) ? '0'.$bucketCounter : $bucketCounter;
        }
        //create final bucket name
        $childBucketName = $newCounter.$bucketBasicName;
        $newBucketName = "$firstString$childBucketName.com";
        /* code to final the bucket name */
        if($newBucketName=="")
        {
            $newBucketName = $duplciateFrom;
        }
        //create string policy for Bucket
        $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';

        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3clientToMove->listBuckets();
        foreach ($contents['Buckets'] as $bucketDetails) {
            if ($newBucketName == $bucketDetails['Name']) {
                $existName = true;
            }
        }
        //if name already exist, then return error message
        if ($existName) {
            $message = "'$newBucketName' bucket already exist, please try with some other name!";
            //return response
            $return = array(
                'value' => '2',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
        else {
            //check index.html file for existing bucket
            $existIndex = false;
            $existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
            foreach ($existingBucket['Contents'] as $existFiles) {
                if ($existFiles['Key'] == 'index.html') {
                    $existIndex = true;
                } else {
                    $existIndex = false;
                }
            }
            //if index file exist, then create bucket
            if($existIndex)
            {
                try{
                    //create instance of NEW server, where we have to move/copy
                    $result3 = $s3clientToMove->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    //list the current bucket from active AWS server, from where we have to move/copy
                    $stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to

                    foreach ($stp['Contents'] as $object) {
                        //create instance of NEW server, where we have to move/copy
                        $s3clientToMove->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );

                    //create instance of NEW server, where we have to move/copy
                    $result2 = $s3clientToMove->putBucketWebsite($arg);
                    $result3 = $s3clientToMove->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    //create instance of NEW server, where we have to move/copy
                    $location = $s3clientToMove->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    $this->create_save_xml_fie($bucketPhoneNumber);
                    $awsFolderPath = "assests/phonenumber.xml";
                    $tmp_name      =  public_path('template_data').DIRECTORY_SEPARATOR."phonenumber.xml";
                    $result 	   = $s3clientToMove->putObject(array(
                        'Bucket'       => $newBucketName,
                        'Key'          => $awsFolderPath,
                        'SourceFile'   => $tmp_name,
                        'ContentType'  => 'application/xml',
                        'ACL'          => 'public-read',
                        'StorageClass' => 'REDUCED_REDUNDANCY',	));

                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'bucket_url' => $newBucketUrl,
                        'bucket_updated_counter' => $newCounter,
                        'bucket_created_server_name' => $toServerName,
                    );
                    return json_encode($return);
                }
                catch(\Exception $exception){

                    $xmlResposne = $exception->getAwsErrorCode();
                    if($xmlResposne=="BucketAlreadyExists")
                    {
                        $message = "Bucket already exists. Please change the URL.";
                    }
                    else
                    {
                        $message = $xmlResposne;
                    }
                    $return = array(
                        'value' => '2',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);

                }

            }
        }
    }
    /*
   * function to create xml file
   * created by NK
   * created on 19 July'17
   * $phoneNumber : From the master bucket table
   */
    public function create_save_xml_fie($phoneNumber='9780058718')
    {
        $xml            = "<?xml version='1.0' encoding='UTF-8'?><phone>$phoneNumber</phone>";
        $xmlFilePath    = public_path('template_data').DIRECTORY_SEPARATOR;
        $file           = fopen($xmlFilePath."phonenumber.xml","w");
        fwrite($file,$xml);
        fclose($file);
    }

    /*
 * function to create xml file
 * created by NK
 * created on 19 July'17
 * $phoneNumber : From the master bucket table
 */
    public function update_phone_xml_fie()
    {
        $phoneNumber            = input::get('phone_number');
        $bucketName             = input::get('bucket_name');
        $region                 = input::get('region');
        $this->create_save_xml_fie($phoneNumber);
        $awsFolderPath          = "assests/phonenumber.xml";
        $tmp_name               = public_path('template_data').DIRECTORY_SEPARATOR."phonenumber.xml";
        $status                 = "Active";
        $allAwsServer           = ConfigAuth::where('status', "=", $status)->first();
        $toServerKey            = $allAwsServer['key'];
        $toServerSecretKey      = $allAwsServer['secret'];
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);
        $result = $s3clientToMove->putObject(array(
            'Bucket'       => $bucketName,
            'Key'          => $awsFolderPath,
            'SourceFile'   => $tmp_name,
            'ContentType'  => 'application/xml',
            'ACL'          => 'public-read',
            'StorageClass' => 'REDUCED_REDUNDANCY',
        ));
        if($result['ObjectURL']!="")
        {
            $message = "Phone Number has been updated successfully";
            flash($message);
            //return response
            $return = array(
                'type' => 'success',
                'message' => $message,
            );
            return json_encode($return);
        }
        else
        {
            $message = "Error in the system. Please wait.";
            flash($message);
            $return = array(
                'type' => 'success',
                'message' => $message,
            );
            return json_encode($return);
        }
    }



}
