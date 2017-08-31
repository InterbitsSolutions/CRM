<?php
namespace App\Http\Controllers;

use App\Models\BucketRegions;
use App\Models\BucketShortCodes;
use App\Models\BucketBrowsers;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;


class BucketFieldsController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /*
     * function to list BUCKET fields
     * created by BK
     */

    public function manageBucketFields($fieldType = null) {
        $fieldType = (!empty($fieldType)) ? $fieldType : "region";
        $fieldsArray = array();
        switch ($fieldType) {
            case 'region':
                $fieldsArray = BucketRegions::get();
                break;
            case 'shortcode':
                $fieldsArray = BucketShortCodes::get();
                break;
            case 'browser':
                $fieldsArray = BucketBrowsers::get();
                break;
            default;
                $fieldsArray = BucketRegions::get();
                break;
        }
        $selectedOptions = array('region' => 'Regions', 'shortcode' => 'Short Codes', 'browser' => 'Browsers');
        return view('adminsOnly.bucketFields.index', compact('fieldType', 'fieldsArray', 'selectedOptions'));
    }

    /*
     * function to ADD BUCKET fields
     * created by BK
     * created on 13th June'17
     */

    public function addField($fieldType = null) {
        $fieldType = (!empty($fieldType)) ? $fieldType : "region";
        $fieldArray = array();
        switch ($fieldType) {
            case 'region':
                $fieldArray = new BucketRegions();
                break;
            case 'shortcode':
                $fieldArray = new BucketShortCodes();
                break;
            case 'browser':
                $fieldArray = new BucketBrowsers();
                break;
            default;
                $fieldArray = new BucketRegions();
                break;
        }
        $selectedOptions = array('region' => 'Region', 'shortcode' => 'Short Code', 'browser' => 'Browser');
        if (!empty($_POST)) {
            //get field values
            $fieldName = $fieldType . '_name';
            $fieldValue = $fieldType . '_value';
            $fieldCode = $fieldType . '_code';
            try {
                //update fields
                $fieldArray->$fieldName = $_POST['field_name'];
                $fieldArray->$fieldValue = strtolower(str_replace(' ', '', $_POST['field_value']));
                if ($fieldType == 'region') {
                    $fieldArray->$fieldCode = str_replace(' ', '', $_POST['field_code']);
                }
                $fieldArray->save();

                flash("$selectedOptions[$fieldType] field added successfully!");
                return redirect('/manage-bucket-fields/' . $fieldType);
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $message = $e->getMessage();
                $errorMessage = 'There is some error while adding bucket field. Please try again later!';	
                flash($errorMessage, "danger");
                return Redirect::to('manage-bucket-fields');
            }
        } else {
            return view('adminsOnly.bucketFields.add', compact('fieldType', 'fieldArray', 'selectedOptions'));
        }
    }

    /*
     * function to EDIT BUCKET fields
     * created by BK
     */

    public function editField($fieldType = null, $recordID) {
        $fieldType = (!empty($fieldType)) ? $fieldType : "region";
        $fieldArray = array();
        switch ($fieldType) {
            case 'region':
                $fieldArray = BucketRegions::find($recordID);
                break;
            case 'shortcode':
                $fieldArray = BucketShortCodes::find($recordID);
                break;
            case 'browser':
                $fieldArray = BucketBrowsers::find($recordID);
                break;
            default;
                $fieldArray = BucketRegions::find($recordID);
                break;
        }
        $selectedOptions = array('region' => 'Region', 'shortcode' => 'Short Code', 'browser' => 'Browser');

        //if user submit the form, then update the field according to user inputs
        if (!empty($_POST)) {
            $fieldName = $fieldType . '_name';
            $fieldValue = $fieldType . '_value';
            $fieldCode = $fieldType . '_code';
            try {
                $fieldArray->$fieldName = $_POST['field_name'];
                $fieldArray->$fieldValue = strtolower(str_replace(' ', '', $_POST['field_value']));
                if ($fieldType == 'region') {
                    $fieldArray->$fieldCode = str_replace(' ', '', $_POST['field_code']);
                }
                $fieldArray->save();
                flash("$selectedOptions[$fieldType] field updated successfully!");
                return redirect('/manage-bucket-fields');
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $message = $e->getMessage();
                $errorMessage = 'There is some error while updating bucket field. Please try again later!';
                flash($errorMessage, "danger");
                return redirect('/manage-bucket-fields');
            }
        } else {
            return view('adminsOnly.bucketFields.edit', compact('fieldType', 'fieldArray', 'selectedOptions'));
        }
    }

    /*
     * function to DELETE BUCKET fields
     * created by BK
     */

    public function deleteField($fieldType = null, $recordID) {
        $activeConfigId = $this->getActiveConfig();
        $whereArray = array('id' => $recordID);
        $fieldType = (!empty($fieldType)) ? $fieldType : "region";
        $fieldArray = array();
        switch ($fieldType) {
            case 'region':
                BucketRegions::where($whereArray)->delete();
                break;
            case 'shortcode':
                BucketShortCodes::where($whereArray)->delete();
                break;
            case 'browser':
                BucketBrowsers::where($whereArray)->delete();
                break;
            default;
                BucketRegions::where($whereArray)->delete();
                break;
        }
        $selectedOptions = array('region' => 'Region', 'shortcode' => 'Short Code', 'browser' => 'Browser');
        flash("$selectedOptions[$fieldType] field deleted successfully!");
        return redirect('/manage-bucket-fields/' . $fieldType);
    }

}
