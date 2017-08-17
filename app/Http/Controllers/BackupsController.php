<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests;
use Aws\S3\S3Client;
use App\Models\MasterBuckets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use mysqli;

class BackupsController extends Controller
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
    
	public function backup(){
      $backup_dir_name = public_path().'/crmbkp/';
      if (!file_exists($backup_dir_name)) {
          mkdir($backup_dir_name, 0777);
      }
      return view('adminsOnly.backup.backup'); 
    }
    public function crmbackup(){
      ini_set('max_execution_time', 3000);
      ini_set('memory_limit','1024M');      
      $path 			= base_path();     
      $application_name = basename($path);
      if($_POST['title'] == 'backup'){
        
         $options = array(
          'db_host'=> env('DB_HOST'),  //mysql host
          'db_uname' => env('DB_USERNAME'),  //user
          'db_password' => env('DB_PASSWORD'), //pass
          'db_to_backup' => env('DB_DATABASE'), //database name
          'db_backup_path' => public_path().'/crmbkp', //where to backup
          'db_exclude_tables' => array() //tables to exclude
        );
        $backup_file_name=$this->backup_mysql_database($options);        
        $current_date = date('Y-m-d');
        $date_extension =  date('d-F-Y', strtotime($current_date));
        //$result = $this->zip(base_path(),public_path().'/crmbkp/'.$application_name.'_'.$date_extension.'.zip');

      }
    }

    public function deletezip($filename){
        unlink(public_path().'/crmbkp/'.$filename);
        flash('File deleted successfully!');
        return Redirect::to("script-backup");      
    }
	
	public function downloadDirectory($filename)
    {
        if(!empty($filename)){
			$downloadpath=public_path().'/crmbkp/';
			//$file_name = 'file.sql';
			$fileurl=$downloadpath.$filename;
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"".$filename."\""); 
			readfile($fileurl);
			
        }
    }
    public function deleteMultipleFiles(){
        if(!empty($_POST)) {
           $filenames = $_POST['file_names'];
          foreach($filenames as $filename){
            unlink(public_path().'/crmbkp/'.$filename);
          }
          $message = "Success ";
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                flash("Files deleted successfully!");
                return json_encode($return);
        }
      }
      public function zipBackup(){
		    $path 			= base_path();  
		    $application_name = basename($path);
		    $current_date = date('Y-m-d');
		    $date_extension =  date('d-F-Y', strtotime($current_date));
        if (file_exists(public_path().'/crmbkp/'.$application_name.'_'.$date_extension.'.zip')) {
            unlink(public_path().'/crmbkp/'.$application_name.'_'.$date_extension.'.zip');
        }
        $result = $this->zip(base_path(),public_path().'/crmbkp/'.$application_name.'_'.$date_extension.'.zip');
        flash("Backup Created successfully!");
        return Redirect::to("script-backup");
    }
      
	
}
