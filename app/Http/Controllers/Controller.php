<?php

namespace App\Http\Controllers;
use App\ConfigAuth;
use Aws\S3\S3Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; 
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use mysqli;


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
            $returnCredentials['aws_counter'] = $getAuthCredentials['aws_counter'];
            $returnCredentials['aws_name'] = $getAuthCredentials['aws_name'];
        }else{
            $returnCredentials['key'] = 'AKIAJLV6DIJLVNQFOYNA';
            $returnCredentials['secret'] = '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';
            $returnCredentials['aws_counter'] = '10000';
            $returnCredentials['aws_name'] = 'default_credentials';
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
        $awsName = $bucketAuthCredentials['aws_name'];
        //create S3 Client
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        //get list of all buckets and check if bucket name already exist
        try{
            $getContent = $s3client->listBuckets();
            $totalBucketCount = 0;
            foreach ($getContent['Buckets'] as $key =>$bucketData) {
                try {
                    $location = $s3client->getBucketLocation(array('Bucket' => $bucketData['Name']));
                    $totalBucketCount++;
                } catch (\Exception $exception) {
                    //catch exception here...
                }
            }
        }
        catch(\Exception $exception){
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
            $totalBucketCount = 0;
//            flash($xmlResponse,'danger');
        }
        return $totalBucketCount;
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
                $totalBucketCount = 0;
                foreach ($getContent['Buckets'] as $key =>$bucketData) {
                    try {
                        $location = $awsObject->getBucketLocation(array('Bucket' => $bucketData['Name']));
                        $totalBucketCount++;
                    } catch (\Exception $exception) {
                        //catch exception here...
                    }
                }
                $totalBuckets = $totalBucketCount;
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
            $returnCredentials['aws_counter'] = $getAuthCredentials['aws_counter'];
            $returnCredentials['aws_name'] = $getAuthCredentials['aws_name'];
        }else{
            $returnCredentials['key'] = 'AKIAJLV6DIJLVNQFOYNA';
            $returnCredentials['secret'] = '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';
            $returnCredentials['aws_counter'] = '10000';
            $returnCredentials['aws_name'] = 'Sandbox@gmail.com';
        }
        return $returnCredentials;
    }


    public function zip($source,$destination){
        $rootPath = realpath($source);
        $folderPath = explode('/',$source);
        $parentFolder = end($folderPath);
        $basepath = base_path();
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($destination, ZipArchive::CREATE);


        // Create recursive directory iterator
        /* @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basepath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($basepath) + 1);

                // Add current file to archive
                if($filePath != "")
                    $zip->addFile($filePath,$relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }



    //db backup
    function backup_mysql_database($options){
        $mtables = array(); $contents = "-- Database: `".$options['db_to_backup']."` --\n";
        //return $options['db_backup_path'];exit;
        $mysqli = new mysqli($options['db_host'], $options['db_uname'], $options['db_password'], $options['db_to_backup']);
        if ($mysqli->connect_error) {
            die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
        }

        $results = $mysqli->query("SHOW TABLES");

        while($row = $results->fetch_array()){
            if (!in_array($row[0], $options['db_exclude_tables'])){
                $mtables[] = $row[0];
            }
        }

        foreach($mtables as $table){
            $contents .= "-- Table `".$table."` --\n";

            $results = $mysqli->query("SHOW CREATE TABLE ".$table);
            while($row = $results->fetch_array()){
                $contents .= $row[1].";\n\n";
            }

            $results = $mysqli->query("SELECT * FROM ".$table);
            $row_count = $results->num_rows;
            $fields = $results->fetch_fields();
            $fields_count = count($fields);

            $insert_head = "INSERT INTO `".$table."` (";
            for($i=0; $i < $fields_count; $i++){
                $insert_head  .= "`".$fields[$i]->name."`";
                if($i < $fields_count-1){
                    $insert_head  .= ', ';
                }
            }
            $insert_head .=  ")";
            $insert_head .= " VALUES\n";

            if($row_count>0){
                $r = 0;
                while($row = $results->fetch_array()){
                    if(($r % 400)  == 0){
                        $contents .= $insert_head;
                    }
                    $contents .= "(";
                    for($i=0; $i < $fields_count; $i++){
                        $row_content =  str_replace("\n","\\n",$mysqli->real_escape_string($row[$i]));

                        switch($fields[$i]->type){
                            case 8: case 3:
                            $contents .=  $row_content;
                            break;
                            default:
                                $contents .= "'". $row_content ."'";
                        }
                        if($i < $fields_count-1){
                            $contents  .= ', ';
                        }
                    }
                    if(($r+1) == $row_count || ($r % 400) == 399){
                        $contents .= ");\n\n";
                    }else{
                        $contents .= "),\n";
                    }
                    $r++;
                }
            }
        }
        // if (!is_dir ( $options['db_backup_path'] )) {
        //         mkdir ( $options['db_backup_path'], 0777, true );
        //  }
        $current_date = date('Y-m-d');
        $filename =  $options['db_to_backup']."_".date('d-F-Y', strtotime($current_date));
        $backup_file_name = $options['db_backup_path'].'/'.$filename.".sql";

        if (file_exists(public_path().'/crmbkp/'.$backup_file_name)) {
            unlink(public_path().'/crmbkp/'.$backup_file_name);
        }
        $fp = fopen($backup_file_name ,'w+');
        if (($result = fwrite($fp, $contents))) {
            //echo "Backup file created '$backup_file_name' ($result)"; 
        }
        fclose($fp);
        return $filename;
    }
	//This is for checked Modules restricted for the User
	public function getModules(){
        if(Auth::user()){
            $role_id = Auth::user()->role;
            $assigned_modules = DB::select('select modules.id,modules.module_name from modules,module_relationships where module_relationships.module_id = modules.id AND module_relationships.role_id='.$role_id);
            return $assigned_modules;
        }
    }
	//Get All Modules for Super Admin
    public function getAllModules(){
        if(Auth::user()){
            $all_modules = DB::select('select modules.id,modules.module_name from modules');
            return $all_modules;
        }
    }
	
	//Getting All url's for the provided Modules
    public function filter($allowedModules){
        if(Auth::user()){
            $allmodules = array();

            foreach($allowedModules as $module){
                array_push($allmodules,$module->module_name);
            }

            $module_names = "'".implode("','",$allmodules)."'";
            $get_urls = DB::select('select module_url from modules WHERE module_name IN('.$module_names.')');
            $to_restricct = array();
            foreach($get_urls as $urls){
                 $to_url = explode(",",$urls->module_url);
                 foreach($to_url as $url){
                    array_push($to_restricct,$url);
                 }
            }
            return array_filter($to_restricct);
        }
    }
	//Functional Approch for getting the urls
    public function resultUrls(){
        $check_superadmin = $this->getRole();
        if($check_superadmin == 1){
            $allowed_module_mw = $this->getAllModules();
        }else{
            $allowed_module_mw = $this->getModules();
        }
        $restrict_module_urls = $this->filter($allowed_module_mw);
        return $restrict_module_urls;
        
    }
    public function getBaseUrl(){
        $urls = Route::getFacadeRoot()->current()->uri();
        if (strpos($urls, '/') !== false) {
            $url_array = explode("/",$urls);
            return $url_array[0];
        }else{
            return Route::getFacadeRoot()->current()->uri();
        }
         
    }
    
    public function getRole(){
        if(Auth::user()){
            return Auth::user()->role;
        }
    }
    public function getName(){
        if(Auth::user()){
            return Auth::user()->name;
        }
    }
    public function clearViewCache(){
      $temp_view_dir =  storage_path().'/framework/views/';
      $files = scandir($temp_view_dir);
      $files = array_diff($files, array('.', '..'));
      foreach($files as $file){
        unlink($temp_view_dir.'/'.$file);
      }
      return true;
    }

}
