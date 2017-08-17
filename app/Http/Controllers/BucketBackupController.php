<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use Aws\S3\S3Client;
use App\Models\User;
use App\Http\Requests;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class BucketBackupController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    /*
     * function to list config credentials for backups
     * created by BK
     * created on 1stAug '17
    */
    public function index()
    {
        $awsBucketsArr = $this->getAwsBuckets();
        return view('adminsOnly.bucketBackup.index', compact('awsBucketsArr'));
    }

    /*
     * function to import buckets for AWS network
     * created by BK
     * created on 1stAug '17
    */
    public function importBuckets()
    {
        if (!empty($_FILES) && !empty($_POST['aws_server'])) {
            $this->uploadBuckets();
        }
//        $awsBucketsArr = $this->getAwsBuckets();
        return view('adminsOnly.bucketBackup.import');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function bucketBackup()
    {
        ini_set('max_execution_time', 6000);
        ini_set('memory_limit','1024M');
        //check the folder and create the backup folder, if not exist
        $realPath = public_path('bucketBackup');
        if (is_dir($realPath)) {
            $this->deleteDir($realPath);
            mkdir($realPath, 0777);
        }else{
            mkdir($realPath, 0777);
        }
        if(!empty($_POST['aws_accounts'])){
            $awsAccount     =  $_POST['aws_accounts'];
            $getAwsDetails  =  DB::table('config_auth')->whereIn('id', array_values($awsAccount))->get();
            //get list of all config auth credentials
//            $configAuth = ConfigAuth::all();
            foreach($getAwsDetails as $key => $authDetails){
                $accountName = $authDetails->aws_name;
                $bucketKey = $authDetails->key;
                $bucketSecret = $authDetails->secret;

                $s3client = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'eu-central-1',
                    'credentials' => [
                        'key'    => $bucketKey,
                        'secret' => $bucketSecret
                    ]
                ]);
                try{
                    //get list of all buckets and check if bucket name already exist
                    $contents = $s3client->listBuckets();
                    //count buckets according to network
                    $bucketBackupArr = array();
                    foreach ($contents['Buckets'] as $key =>$bucketData){
                        if (preg_match('/www/',$bucketData['Name'])){
                            $bucketName = $bucketData['Name'];
                            //get bucket location
                            try{
                                $location = $s3client->getBucketLocation(array(
                                    'Bucket' => $bucketName
                                ));
                                //get bucket first string
                                $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                                $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);
                                $getUniqueNumber = $this->getNumericVal($replaceCommonString);
                                if(!empty($getUniqueNumber)) {
                                    $finalString = preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
                                }else{
                                    $finalString = $replaceCommonString;
                                }
                                if(array_key_exists($finalString,$bucketBackupArr)){
                                    $bucketBackupArr[$finalString]['bucket_name'] = $bucketName;
                                    $bucketBackupArr[$finalString]['bucket_region'] = $location['LocationConstraint'];
                                }else{
                                    $bucketBackupArr[$finalString]['bucket_name'] = $bucketName;
                                    $bucketBackupArr[$finalString]['bucket_region'] = $location['LocationConstraint'];
                                }
                            } catch(\Exception $exception){

                            }
                        }
                    }

                    //get files and folder structure of the Buckets
                    foreach($bucketBackupArr as $bucketNetworkName => $bucketDetails){
                        $bucketName = $bucketDetails['bucket_name'];
                        $bucketLocation = $bucketDetails['bucket_region'];
                        $bucketNetworkName = (!empty($bucketNetworkName)) ? $bucketNetworkName : $bucketName;

//                        echo "<br><b>Files for :$bucketName</b><br/>";
                        //create object according to bucket region
                        $s3client = new S3Client([
                            'version'     => 'latest',
                            'region'      => $bucketLocation,
                            'credentials' => [
                                'key'    => $bucketKey,
                                'secret' => $bucketSecret
                            ]
                        ]);
                        $existingBucket = $s3client->listObjects(array('Bucket' => $bucketName));

                        $accountName = trim($accountName);
                        //folder path
                        $rootFolderPath = public_path('bucketBackup') . DIRECTORY_SEPARATOR .$accountName;
                        $bucketNetworkPath = $rootFolderPath.DIRECTORY_SEPARATOR.$bucketNetworkName.DIRECTORY_SEPARATOR;
                        $bucketAssestsPath = $rootFolderPath.DIRECTORY_SEPARATOR.$bucketNetworkName.DIRECTORY_SEPARATOR.'assests'.DIRECTORY_SEPARATOR;

                        //check if rootfolder exist or not, then create a folder
//                        if(!is_dir($rootFolderPath)){
                        if(!file_exists($rootFolderPath)){
                            mkdir($rootFolderPath, 0777);
                        }
                        //upload files for the selected network
//                        if(!is_dir($bucketNetworkPath)){
                        if(!file_exists($bucketNetworkPath)){
                            mkdir($bucketNetworkPath, 0777);
                            mkdir($bucketAssestsPath, 0777);
                        }
                        if (!empty($existingBucket['Contents'])) {
                            foreach ($existingBucket['Contents'] as $object) {
                                $fileName = $object['Key'];
                                $urlConcatOperator = ($bucketLocation=='ap-northeast-1') ? '-' : '.';
                                $bucketURL = "http://".$bucketName.".s3-website".$urlConcatOperator.$bucketLocation.".amazonaws.com/".$fileName;
                                if($this->isFile($bucketURL)){
                                    $this->get_file($bucketURL, $bucketNetworkPath, $fileName);
                                }else{
                                    $fileStructure = explode('/', $fileName);
//                                    foreach($fileStructure as $folderName){
//                                        $bucketFoldersPath = $bucketNetworkPath.$folderName;
//                                        //upload files for the selected network
////                                        if(!is_dir($bucketFoldersPath)){
//                                        if(!file_exists($bucketFoldersPath)){
//                                            mkdir($bucketFoldersPath, 0777);
//                                        }
//                                    }
                                    if(!file_exists($fileName)){
                                        mkdir($fileName, 0777);
                                    }
                                }
                            }
                        }
                    }
                }
                catch (\Aws\S3\Exception\S3Exception $e) {
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => 'There is some error while taking backup!!',
                    );
                    $message = "There is some error while taking backup!!";
                    flash($message);
                    //return json_encode($return);
                    return Redirect()->route('backup-complete');
                }
            }
            $realPath = public_path('bucketBackup');
            $createZip = $this->zipBackup($realPath);
            //return response
//            flash('Backup taken successfully');
            $return = array(
                'value' => '100',
                'type' => 'success',
                'message' => 'Backup taken successfully',
            );
            $message = "Backup taken successfully";
            flash($message);
            //return json_encode($return);

            return Redirect()->route('backup-complete');
        }

    }
    /*
     *
     */
    public function backupComplete()
    {
        return view('adminsOnly.bucketBackup.backup-complete');
    }
    /*
    * function to check whether link is file or folder
    * created by BK
    * created on 1stAug '17
    */
    function isFile($url)
    {
        $options['http'] = array(
            'method' => "HEAD",
            'ignore_errors' => 1,
            'max_redirects' => 0
        );
        $body = file_get_contents($url, NULL, stream_context_create($options));
        sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $code);
        return $code === 200;
    }

    /*
     * function to save file from URL via CURL
     * created by BK
     * created on 1stAug '17
     */
    function get_file($file, $local_path, $newfilename)
    {
        $err_msg = '';
//        echo "<br>Attempting message download for $file<br>";
        $out = fopen($local_path.$newfilename,"wb");
        if ($out == FALSE){
            print "File not opened<br>";
            exit;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FILE, $out);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_exec($ch);
//        echo "<br>Error is : ".curl_error ( $ch);
        curl_close($ch);
        //fclose($handle);
    }
    /*
     * function to create ZIP of Buckets files/folder
     * created by BK
     * created on 1stAug '17
     */
    function zipBackup($realPath){
        $fileName = 'bucketBackup.zip';
        // Get real path for our folder
        $rootPath = realpath($realPath);
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($fileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($realPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        if (file_exists($realPath.DIRECTORY_SEPARATOR.$fileName)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
            header('Content-Length: ' . filesize($fileName));
            flush();
            readfile($fileName);
            unlink($fileName);
        }
        $zip->close();
    }
    /*
     * function to get value after numeric from string
     * created on 1stAug '17
     */
    public function getNumericVal ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return (!empty($matches[0][0])) ? $matches[0][0] : '';
    }

    /*
     * function to delete directory
     * created by BK
     * created on 1stAug '17
     */
    public static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /*
     * function to import the Bucket files and into DB
     * created by BK
     * created on 3rd Aug
     */
    /*
     * function to upload drag documents
     * Created by BK
     * Created on 12th April
     */
    public function uploadBuckets()
    {
        ini_set('upload_max_filesize', "20M");
        ini_set('max_execution_time', 6000);
        ini_set('memory_limit','1024M');
        //upload file case
        if (!empty($_FILES) && !empty($_POST['aws_server'])) {
            if (!empty($_FILES['files']['name'])) {
                //check if IMPORT DIR found or not
                $importFor = $_POST['aws_server'];
                $realPath = public_path('importBuckets');
                if (is_dir($realPath)) {
                    $this->deleteDir($realPath);
                    mkdir($realPath, 0777);
                }else{
                    mkdir($realPath, 0777);
                }
                //get auth credentials for selected AWS server
                $bucketAuthCredentials = $this->getCredentials($importFor);
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
                $uploadDirectory = public_path('importBuckets');
                //check file name and size!
                $max_upload_size = 20000000;
                $fileName = $_FILES['files']['name'];
                $fileSize = $_FILES['files']['size'];
                //check if file size greater
                if ($fileSize > $max_upload_size) {
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => "File Size is too Big!",
                    );
                    return json_encode($return);
                } else {
                    $fileType = array('zip');
//                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $path_parts = pathinfo($_FILES["files"]["name"]);
                    $extension = $path_parts['extension'];

                    //check extension type
                    if (in_array($extension, $fileType)) {
                        //change upload dir
                        $sourcePath = $_FILES['files']['tmp_name'];       // Storing source path of the file in a variable
                        $ext = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
//                        $fileNewName = md5(uniqid(rand(), true)) . '.' . $ext;
//                        $fileNewName = $fileName;
                        $targetPath = $uploadDirectory .DIRECTORY_SEPARATOR. $fileName;// Target path where file is to be stored
                        try {
                            if(move_uploaded_file($sourcePath, $targetPath)){ // Moving Uploaded file
                                //UNZIP the FILE
                                // get the absolute path to $file
                                $path = pathinfo($targetPath, PATHINFO_DIRNAME);
                                $zip = new \ZipArchive;
                                $res = $zip->open($targetPath);
                                if ($res === TRUE) {
                                    // extract it to the path we determined above
                                    $zip->extractTo($path);
                                    $zip->close();
                                    //import buckets to new SERVER
                                    $this->importBucketsToServer($importFor, $uploadDirectory);
                                } else {
                                    //error message
                                    $flashMsg = "There is some error while processing!";
                                    flash($flashMsg, "danger");
                                    return redirect('/import-buckets');
                                }
                            }
                            else {
                                //error message
                                $flashMsg = "File not uploaded";
                                flash($flashMsg, "danger");
                                return redirect('/import-buckets');
                            }
                        }
                        catch(\Exception $exception){
                            //error message
                            $flashMsg = $exception->getMessage();
                            flash($flashMsg, "danger");
                            return redirect('/import-buckets');
                        }
                    } else {
                        //error message
                        $flashMsg = "Please upload valid Extensions file only!";
                        flash($flashMsg, "danger");
                        return redirect('/import-buckets');
                    }
                }
            } else {
                //error message
                $flashMsg = "Please try again later!";
                flash($flashMsg, "danger");
                return redirect('/import-buckets');
            }
        } else {
            //error message
            $flashMsg = "Please try again later!";
            flash($flashMsg, "danger");
            return redirect('/import-buckets');
        }
    }
    /*
     * List Bucket files and folder structure
     * created by BK
     * created on 3rd Aug'17
     */
    public function fillArrayWithFileNodes( \DirectoryIterator $dir )
    {
        $data = array();
        foreach ( $dir as $node )
        {
            if ( $node->isDir() && !$node->isDot() )
            {
                $data[$node->getFilename()] = $this->fillArrayWithFileNodes( new \DirectoryIterator( $node->getPathname() ) );
            }
            else if ( $node->isFile() )
            {
                $data[] = $node->getFilename();
            }
        }
        return $data;
    }

    /*
     * Function to get mime type
     * created by BK
     * created on 3rd Aug
     */
    public function get_mime_type($filename) {
        $idx = explode( '.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }

    /*
     * Import buckets in new server
     * created by BK
     * created on 3rd Aug'17
     */
    public function importBucketsToServer($authID, $uploadDirectory){
        //get folder name
        $bucketFilesStructure = $this->fillArrayWithFileNodes( new \DirectoryIterator( $uploadDirectory ) );
        $folderName = array_filter(array_keys($bucketFilesStructure));
        if(!empty($folderName)){
            foreach ($folderName as $key => $folderName){
                $uploadDirectory = $uploadDirectory.DIRECTORY_SEPARATOR.$folderName;
            }

            //get auth credentials for selected AWS server
            $bucketAuthCredentials = $this->getCredentials($authID);
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $bucketCounter = $bucketAuthCredentials['aws_counter'];
            $bucketAwsName = $bucketAuthCredentials['aws_name'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => 'eu-central-1',
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //use try catch to manage the files and folder structure
            try {
                // Using operation methods creates command implicitly.
                $bucketArray = $s3client->listBuckets();
                //upload DIR path
                $bucketFilesStructure = $this->fillArrayWithFileNodes( new \DirectoryIterator( $uploadDirectory ) );
                $importBucketsArr = array();
                foreach($bucketFilesStructure as $bucketRootFolder => $innerFiles){
                    //check if bucket exist, then start with latest counter
                    if(!empty($bucketArray['Buckets'])){
                        $matchCases =  array();
                        foreach($bucketArray['Buckets'] as $bucketDetail) {
                            if (strpos($bucketDetail['Name'], $bucketRootFolder) !== false) {
                                $matchCases[] = $bucketDetail;
                            }
                        }
                        $getLastRecord = end($matchCases);
                        //get final string from bucket name
                        $firstString = substr($getLastRecord['Name'], 0, strcspn($getLastRecord['Name'], '1234567890'));
                        $getLastEntry = str_replace(array($firstString,'.com', $bucketRootFolder), '' , $getLastRecord['Name']);
                        $incrementRecord = $getLastEntry+1;
                        $bucketCounter = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
                    }
                    //create bucket final name with updated counter
                    $importBucketName = "www.support.microsoft$bucketCounter$bucketRootFolder.com";
                    $importBucketsArr[] = $importBucketName;
                    /**************************create bucket while IMPORT***************************/
                    $stringPolicy = '{
                        "Version": "2012-10-17",
                        "Statement": [
                            {
                                "Sid": "Allow Public Access to All Objects",
                                "Effect": "Allow",
                                "Principal": "*",
                                "Action": "s3:GetObject",
                                "Resource": "arn:aws:s3:::' . $importBucketName . '/*"
                            }
                        ]
                    }';
                    $s3client->createBucket([
                        'Bucket' => $importBucketName,
                    ]);
                    $arg = array(
                        'Bucket' => $importBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );
                    $s3client->putBucketWebsite($arg);
                    $s3client->putBucketPolicy([
                        'Bucket' => $importBucketName,
                        'Policy' => $stringPolicy,
                    ]);
                    $message = "'$importBucketName' bucket has been added successfully!";
                    /**************************end of create bucket while IMPORT***************************/

                    /**************************IMPORT FILES and FOLDERS***************************/
                    //for index.html
                    foreach ($innerFiles as $key => $fileName){
                        if($fileName=='index.html'){
                            $filePath = $uploadDirectory.DIRECTORY_SEPARATOR.$bucketRootFolder.DIRECTORY_SEPARATOR.$fileName;
                            $contentType = $this->get_mime_type($filePath);
                            // Upload data.
                            $result = $s3client->putObject(array(
                                'Bucket'       => $importBucketName,
                                'Key'          => $fileName, //filename
                                'SourceFile'   => $filePath,
                                'ContentType'  => $contentType,
                                'ACL'          => 'public-read',
                                'StorageClass' => 'REDUCED_REDUNDANCY',
                            ));
                            //check if folder created or not
                            if($result['ObjectURL']){
                                //upload success
                            }else{
                                //error message
                                $flashMsg = "There is some error while processing with html file!";
                                flash($flashMsg, "danger");
                                return redirect('/import-buckets');
                            }
                        }
                    }
                    //get assests files
                    if(!empty($innerFiles['assests'])){
                        // create folder for ASSESTS under buckets
                        $filePath = $uploadDirectory.DIRECTORY_SEPARATOR.$bucketRootFolder.DIRECTORY_SEPARATOR.'assests'.DIRECTORY_SEPARATOR.$innerFiles['assests'][0];
                        $s3client->putObject(array(
                            'Bucket'       => $importBucketName,
                            'Key'          => 'assests/', //filename
                            'SourceFile'   => $filePath,
                            'ContentType'  => $contentType,
                            'ACL'          => 'public-read',
                            'StorageClass' => 'REDUCED_REDUNDANCY',
                        ));
                        foreach ($innerFiles['assests'] as $folder => $fileName){
                            $filePath = $uploadDirectory.DIRECTORY_SEPARATOR.$bucketRootFolder.DIRECTORY_SEPARATOR.'assests'.DIRECTORY_SEPARATOR.$fileName;
                            $contentType = $this->get_mime_type($filePath);
                            // Upload data.
                            $result = $s3client->putObject(array(
                                'Bucket'       => $importBucketName,
                                'Key'          => 'assests/'.$fileName, //filename
                                'SourceFile'   => $filePath,
                                'ContentType'  => $contentType,
                                'ACL'          => 'public-read',
                                'StorageClass' => 'REDUCED_REDUNDANCY',
                            ));
                            //check if folder created or not
                            if($result['ObjectURL']){
                                //upload success
                            }else{
                                //error message
                                $flashMsg = "There is some error while processing with assests folder!";
                                flash($flashMsg, "danger");
                                return redirect('/import-buckets');
                            }
                        }
                    }
                }
                //success message
                $flashMsg = "Buckets Import successfully for $bucketAwsName account!";
                flash($flashMsg);
                return redirect('/import-buckets');
            }
            catch(\Exception $exception){
                $flashMsg = "There is some error while processing with '$bucketAwsName' server, please try again later!";
                flash($flashMsg, "danger");
                $message = $exception->getMessage();
                return redirect('/import-buckets');
            }

        }else{
            //success message
            $flashMsg = "There is some error while proceeding, please check folder structure!";
            flash($flashMsg, "danger");
            return redirect('/import-buckets');
        }
    }
}
