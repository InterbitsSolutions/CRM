<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests;
use App\Models\Bucket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use Aws\S3\S3Client;

use App\Classes\S3;





/*if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJLV6DIJLVNQFOYNA');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

S3::setAuth(awsAccessKey, awsSecretKey);*/

class DummyBucketsController extends Controller
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
    public function index(Bucket $bucket)
    {
       /* $bucket= new Bucket($request->all());*/
	    //$bucketr = $bucket->bucketlist();
    	//$contents="ss";
        //create object for "S3Client"
        $keyCredentials = $this->getAuthCredentials();
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $keyCredentials['key'],
                'secret' => $keyCredentials['secret'],
            ]
        ]);
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        // Using operation methods creates command implicitly.
        $contents = $s3client->listBuckets();
        return view('adminsOnly.dummyBuckets.index', compact('contents'));
    }

    /*
     * function to make bucket duplicate
     * created by BK
     * created on 2nd June'17
     */
    public function duplicator(){
        if(!empty($_POST)) {
            $bucket = $_POST['duplicate_for'];
            $newBucketName = $_POST['bucket_name'];
            $newBucketRegion = $_POST['bucket_region'];

            //create object for "S3Client"
            $keyCredentials = $this->getAuthCredentials();
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => 'eu-central-1',
                'credentials' => [
                    'key'    => $keyCredentials['key'],
                    'secret' => $keyCredentials['secret'],
                ]
            ]);
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
                   
                    $message = "$newBucketName bucket successfully created!";
                    //return response
                    $return = array(
                        'type' => 'success',
                        'message' => $message,
                    );
                    return json_encode($return);

                } else {
                    $message = "Index.html file must be in your exisiting bucket, please add and try again later!";
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
     * function to delete bucket
     * created by BK
     * created on 2nd June'17
     */
    public function deleteBucket(){
        if(!empty($_POST)) {
            $newBucketName = $_POST['bucket_name'];
            if(!empty($newBucketName)){
                //create object for "S3Client"
                $keyCredentials = $this->getAuthCredentials();
                $s3client = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'eu-central-1',
                    'credentials' => [
                        'key'    => $keyCredentials['key'],
                        'secret' => $keyCredentials['secret'],
                    ]
                ]);
                die('ready to delete bucket');
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
}
