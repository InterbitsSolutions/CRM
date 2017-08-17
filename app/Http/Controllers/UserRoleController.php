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
use Illuminate\Database\Query\Builder;

use App\Models\UserRoles;
use App\Models\Modules;
use App\Models\ModuleRelationships;

/*if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJLV6DIJLVNQFOYNA');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

S3::setAuth(awsAccessKey, awsSecretKey);*/

class UserRoleController extends Controller
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
	 
	 /*
    public function index()
    {
        
    }
	*/
   

    /*
     * function to get the network hit list
     * created by Swati Mishra
     * created on 03 Aug 17
     * return : array
     */
	 
	 public function addRole(){
       //$activeConfigId = $this->getActiveConfig();
	   //$addRole->aws_server_id = $activeConfigId;
	     $module_arr = array();
		 $modules = Modules::get()->all();
		 foreach($modules as $module){
		  $module_arr[$module->id] = $module->module_name;      
		 }
	 
       if(!empty($_POST)){
            $addRole = new UserRoles();
            $addRole->role_name = $_POST['role_name'];
            //$addRole->created_at = date('Y-m-d H:i:s');
            $addRole->save();
			$insertedId = $addRole->id;
			
			foreach($_POST['modules'] as $module){
			   $assignModule = new ModuleRelationships();
			   $assignModule->role_id = $insertedId;
			   $assignModule->module_id=$module;
			   $assignModule->save();
			}
	  
            $message = " Role Added successfully!";
            flash($message);
            return Redirect::to("list-user-roles");
	    }
	   
       return view('adminsOnly.Roles.addRole', compact('module_arr')); 
            
        
    }
	
	public function listRoles(){
        $activeConfigId = $this->getActiveConfig();
        $userRoles = UserRoles::get();
        //echo '<pre>';print_r($userRoles);exit;
        return view('adminsOnly.Roles.rolelist', compact('userRoles'));
    } 
	
	public function deleteRole($roleID)
    {
        if(!empty($roleID)){
            $whereArray = array('id'=>$roleID);
            //delete files from DB
            UserRoles::where($whereArray)->delete();
            flash('Role deleted successfully!');
            return Redirect::to("list-user-roles");
        }
    }
	
	
	
    public function editRole($id=null){
       $activeConfigId = $this->getActiveConfig();
	   
	    
	    $editmodule = array();
		 $modules = Modules::get()->all();
		 foreach($modules as $module){
		  $module_arr[$module->id] = $module->module_name;      
		 }
		 $condition = ['role_id'=>$id];
		 $editDetails = ModuleRelationships::where($condition)->get();
		 foreach($editDetails as $edit_module){
		  array_push($editmodule,$edit_module->module_id);
		 }
		 
        if(!empty($_POST)){
            $role_name = $_POST['role_name'];
            
            $editRoles = UserRoles::find($id);
            //$editRoles->aws_server_id = $activeConfigId;
            $editRoles->role_name  = $role_name;
            $editRoles->save();
			
			$whereArray = array('role_id'=>$id);
            ModuleRelationships::where($whereArray)->delete();
			
			foreach($_POST['modules'] as $module){
			   $assignModule = new ModuleRelationships();
			   $assignModule->role_id = $id;
			   $assignModule->module_id=$module;
			   $assignModule->save();
			}
			
			
	  
            $message = " Role Updated successfully!";
            flash($message);
            return Redirect::to("list-user-roles");
            
            
        }
        $userRoles = UserRoles::findOrFail($id);
        return view('adminsOnly.Roles.editRole', compact('userRoles','editmodule','module_arr'));
    }
	
	
	public function viewRole($id)
	{
		
	$editmodule = array();
		 $modules = Modules::get()->all();
		 foreach($modules as $module){
		  $module_arr[$module->id] = $module->module_name;      
		 }
		 $condition = ['role_id'=>$id];
		 $editDetails = ModuleRelationships::where($condition)->get();
		 foreach($editDetails as $edit_module){
		  array_push($editmodule,$edit_module->module_id);
		 }
	
	$viewUserRoles = UserRoles::findOrFail($id);
    return view('adminsOnly.Roles.viewRole', compact('viewUserRoles','module_arr','editmodule'));
	
		
	}
	
 
    
	
	
	
	
}
