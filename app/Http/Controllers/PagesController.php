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
        $totalBucketCount = 0;
        $masterBucketCount = 0;
        return view('dashboard.home', ['user' => Auth::user(), 'masterBucketCount'=>$masterBucketCount, 'totalBucketCount'=>$totalBucketCount]);
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

    /*
     * function to get Dashboard Content
     * created by BK
     * created on 24th Aug'17
     */
    public function getDashboardContent(){
        //create object for S3client
        $s3Obj = $this->s3clientObject();
        $masterBucketCount = MasterBuckets::count();
        $contents = $s3Obj->listBuckets();
        $data = array();
        $totalBuckets = 0;
        foreach ($contents['Buckets'] as $key =>$bucketData){
            try
            {
                $s3Obj->getBucketLocation(array('Bucket' => $bucketData['Name'] ));
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
                $totalBuckets++;
            }
            catch(\Exception $exception){
            }
        }
        //create HTML for buckets
        if(!empty($data)){
            rsort($data);
            $bucketHTML = '';
            foreach($data as $dataKey => $dataVal) {
                $bucketName = ($dataVal[0]!=""?$dataVal[0]:"N/A");
                $bucketCounter = count($dataVal);
                $bucketUrl = url('/buckets?type=dashboard&x=').$bucketName .('&bcnt=').$bucketCounter;
                $counterClass = ($bucketCounter>10) ? "bucket-count count10" : "bucket-count";
                $bucketClass = ($bucketCounter>10) ? "bucket-icon bucket10" : "bucket-icon";
                $bucketHTML .= '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                                    <a href="'.$bucketUrl.'">
                                        <div class="bucket_bg_value">
                                            <span class="'.$counterClass.'">'.$bucketCounter.'</span>
                                            <div class="'.$bucketClass.'"><i class="fa fa-shopping-basket fa-2x"></i></div>
                                            <div class="text-bucket">'.$bucketName.'</div>
                                        </div>
                                    </a>
                                 </div>';
            }
        }
        //get AWS bucket Array for Donut graph
        $awsBucketsArr = $this->getAwsBuckets();

        //return response
        $return = array(
            'type' => 'success',
            'masterBuckets' => $masterBucketCount,
            'totalBuckets' => $totalBuckets,
            'awsBucketData' => $awsBucketsArr,
            'bucketHTML' => $bucketHTML
        );
        return json_encode($return);
    }
}
