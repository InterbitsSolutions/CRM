<?php
namespace App\Http\Controllers;

use App\Models\BucketParams;
use App\Models\BucketRegions;
use App\Models\BucketShortCodes;
use Illuminate\Support\Facades\Redirect;


class BucketParamsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /*
    * function to list bucket params
    * created by BK
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
                $addBucketParam               = new BucketParams();
                $addBucketParam->bucket_region  = $bucketRegion;
                $addBucketParam->bucket_short_code  = $bucketShortCode;
                $addBucketParam->bucket_parameters  = $bucketParameters;
                $addBucketParam->save();
                
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
    */
    public function editBucketParams($id)
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketParameters = $_POST['bucket_parameters'];
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
     */
    public function deleteBucketParams($bucketID)
    {
        if(!empty($bucketID)){
            $whereArray = array('id'=>$bucketID);
            BucketParams::where($whereArray)->delete();
            flash('Bucket params deleted successfully!');
            return Redirect::to("list-bucket-params");
        }
    }
}
