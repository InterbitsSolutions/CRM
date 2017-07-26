<?php

namespace App\Http\Controllers;
use App\ConfigAuth;
use Aws\S3\S3Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /*
    * function to get auth credentials
    * created by BK
    * created on 2nd June
    */
    public function getAuthCredentials(){
        $getAuthCredentials = ConfigAuth::where('status', "=", 'active')->first();
        $returnCredentials = array();
        if(!empty($getAuthCredentials)){
            $returnCredentials['key'] = $getAuthCredentials['key'];
            $returnCredentials['secret'] = $getAuthCredentials['secret'];
        }else{
            $returnCredentials['key'] = 'AKIAJLV6DIJLVNQFOYNA';
            $returnCredentials['secret'] = '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';
        }
        return $returnCredentials;
    }
    /*
    * function to get count of Buckets in AWS
    * created by BK
    * created on 12th June
    */
    public function countBuckets(){
        //create object for "S3Client"
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3client->listBuckets();
        return count($contents['Buckets']);
    }

    /*
     * function to get current active Config id
     * created by BK
     * created on 30th June'17
     */
    public function getActiveConfig()
    {
        $getActiveConfig = ConfigAuth::where('status', "=", 'active')->first();
        $activeConfigId = (!empty($getActiveConfig->id)) ? $getActiveConfig->id : 1;
        return $activeConfigId;
    }

    /*
     * function to get current active Config counter for BUCKETS
     * created by BK
     * created on 6th July'17
     */
    public function getConfigCounter()
    {
        $getActiveConfig = ConfigAuth::where('status', "=", 'active')->first();
        $activeCounter = (!empty($getActiveConfig->aws_counter)) ? $getActiveConfig->aws_counter : 1;
        return $activeCounter;
    }

    /*
     * function to get AWS bucket Counter
     * created by BK
     * created on 7th July'17
     */
    public function getAwsBuckets(){
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
                $awsNetworkArr[$configDetails['aws_name']]['id'] = $configDetails['id'];
                $awsNetworkArr[$configDetails['aws_name']]['label'] = $configDetails['aws_name'];
                $awsNetworkArr[$configDetails['aws_name']]['aws_name'] = $configDetails['aws_name'];
                $awsNetworkArr[$configDetails['aws_name']]['status'] = $configDetails['status'];
                $awsNetworkArr[$configDetails['aws_name']]['value'] = $totalBuckets;
            }
            catch(\Exception $exception){
                //catch exception here...
            }
        }
        return array_values($awsNetworkArr);
    }


    /*
     * function to get auth credentials by passing value
     * created by BK
     * created on 25th July
   */
    public function getCredentials($authRecordID){
        $getAuthCredentials = ConfigAuth::where('id', "=", $authRecordID)->first();
        $returnCredentials = array();
        if(!empty($getAuthCredentials)){
            $returnCredentials['key'] = $getAuthCredentials['key'];
            $returnCredentials['secret'] = $getAuthCredentials['secret'];
        }else{
            $returnCredentials['key'] = 'AKIAJLV6DIJLVNQFOYNA';
            $returnCredentials['secret'] = '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';
        }
        return $returnCredentials;
    }

}
