<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests;
use Aws\S3\S3Client;
use App\Models\MasterBuckets;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //create object for "S3Client"
        $awsBucketsArr = $this->getAwsBuckets();
		$bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $awsName = $bucketAuthCredentials['aws_name'];

        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        $graphColorCodes = array('#fb9678', '#01c0c8', '#4F5467', '#00c292', '#03a9f3', '#ab8ce4', '#13dafe', '#99d683', '#B4C1D7');
        //get list of all buckets and check if bucket name already exist
        try{
            $contents 		  = $s3client->listBuckets();
            $masterBucketCount = MasterBuckets::count();
            //count buckets according to network
            $data = array();
            $totalBucketCount = 0;
            foreach ($contents['Buckets'] as $key =>$bucketData){
                try
                {
                    $location = $s3client->getBucketLocation(array('Bucket' => $bucketData['Name'] ));
                    if (preg_match('/www/',$bucketData['Name'])){
                        $bucketName = $bucketData['Name'];
                        //get bucket first string
                        $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                        $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);
                        $getUniqueNumber = $this->getNumericVal($replaceCommonString);
                        if(!empty($getUniqueNumber)) {
                            $finalString = preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
                        }else{
                            $finalString = $replaceCommonString;
                        }
                        if(array_key_exists($finalString,$data)){
                            $data[$finalString][] = $finalString;
                        }else{
                            $data[$finalString][] = $finalString;
                        }
                    }
                    $totalBucketCount++;
                }
                catch(\Exception $exception){
                }
            }
        }
        catch(\Exception $exception){
            //set default params
            $totalBucketCount = 0;
            $masterBucketCount = 0;
            $data = array();
            $contents = array();
            $contents['Buckets'] = array();

            //return response
            $xmlResponse = $exception->getAwsErrorCode();
            if($awsName=='default_credentials'){
                if(Auth::check()) {
                    // user is logged in
                    flash('Please active Aws Configuration first to process further! ','danger');
                }
            }else{
//                flash($xmlResponse,'danger');
                flash('Please active a valid Aws Configuration to process further! ','danger');
            }
        }
        return view('dashboard.home', ['user' => Auth::user(),'contents'=>$contents,'masterBucketCount'=>$masterBucketCount, 'awsBucketsArr'=>$awsBucketsArr, 'graphColorCodes'=>$graphColorCodes,'data'=>$data,'totalBucketCount'=>$totalBucketCount]);
    }

    public function showUpdatePasswordForm()
    {
        return view('dashboard.updatePassword');
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'currentPassword' => 'required',
            'password'        => 'required|same:confirmPassword|min:6',
            'confirmPassword' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $check = Auth::validate([
                'email'    => Auth::user()->email,
                'password' => $request->currentPassword
            ]);
            if (!$check) :
                $validator->errors()->add('current_password', 'Your current password is incorrect.');
            endif;
        });

        if ($validator->passes()) {
            Auth::user()->password = Hash::make($request->password);
            Auth::user()->save();
            flash('Your password was updated!');
            return back();
        }
        return back()->withErrors($validator);
    }

	public function getNumericVal ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return (!empty($matches[0][0])) ? $matches[0][0] : '';
    }
}
