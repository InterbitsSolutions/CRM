<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use App\Models\BucketRegions;
use App\Models\BucketTemplates;
use App\Models\MasterBucketsCounter;
use App\Models\TemplateFiles;
use App\Models\TemplateFolders;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Aws\S3\S3Client;
use Storage;

class TemplatesController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
	public function delete_files($files,$folderName="")
	{

		if(!empty($folderName))
		{
			$folderNameArr[]=$folderName;
		}
		foreach($files as $file)
		{ 
			if(is_file($file))
			{
				unlink($file); // delete file
			}
			else
			{		
				$folderName	= $file;	 
				$files 		= glob($file.'/*'); // get all file names					
				$this->delete_files($files,$folderName);
			}
		}
		if(!empty($folderNameArr))
		{
			foreach($folderNameArr as $folderVal)
			{
				rmdir(public_path('template_data') . DIRECTORY_SEPARATOR . $folderVal);
			}			
		}
	}
    /*
        * function to add Templates
        * created by BK
        * created on 9th June'17
        */
    public function addTemplate()
    {
        if(!empty($_POST)){
            //get params
            $awsName = strtolower($_POST['aws_name']);
            $templateName = $_POST['template_name'];
            $templateRegion = $_POST['template_region'];
            //get region code from - required
			 $checkTemplateExist = BucketTemplates::where('aws_name', "=", $awsName)->first();
            if(empty($checkTemplateExist)) {
                //create string policy for Bucket
                $stringPolicy = '{
                    "Version": "2012-10-17",
                    "Statement": [
                        {
                            "Sid": "Allow Public Access to All Objects",
                            "Effect": "Allow",
                            "Principal": "*",
                            "Action": "s3:GetObject",
                            "Resource": "arn:aws:s3:::' . $awsName . '/*"
                        }
                    ]
                }';
                //create object for "S3Client"
                $primary                = "yes";
                $bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();
                $activeConfigId 		=  $bucketAuthCredentials['id'];
                $bucketKey = $bucketAuthCredentials['key'];
                $bucketSecret = $bucketAuthCredentials['secret'];
                $s3client = new S3Client([
                    'version' => 'latest',
                    'region' => $templateRegion,
                    'credentials' => [
                        'key' => $bucketKey,
                        'secret' => $bucketSecret
                    ]
                ]);

                //get list of all buckets and check if bucket name already exist
				try
				{	
				$existName = false;
                $contents = $s3client->listBuckets();
//                $contents = array();
//                $contents['Buckets'] = array();
                foreach ($contents['Buckets'] as $bucketDetails) {
                    if ($awsName == $bucketDetails['Name']) {
                        $existName = true;
                    }
                }
                //if name already exist, then return error message
                if ($existName) {
                    $message = "'$awsName' already exist, please try with some other name!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                   // return json_encode($return);					
					flash($message, 'danger');
					return Redirect::to('add-template');
                } else {
                    $s3client->createBucket([
                        'Bucket' => $awsName,
                    ]);
                    $arg = array(
                        'Bucket' => $awsName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );
                    $s3client->putBucketWebsite($arg);
                    $s3client->putBucketPolicy([
                        'Bucket' => $awsName,
                        'Policy' => $stringPolicy,
                    ]);
                    //add bucket in DB
                    $addBucket = new BucketTemplates();
                    $addBucket->aws_server_id = $activeConfigId;
                    $addBucket->template_name = $templateName;
                    $addBucket->template_region = $_POST['template_region'];
                    $addBucket->aws_name = $awsName;
                    $addBucket->save();
                    $insertedId = $addBucket->id;
                    $message = "'$templateName' Template has been added successfully!";
                    flash($message);
					//unset and create DIR
					$folderPath = public_path('template_data') . DIRECTORY_SEPARATOR . $insertedId;
					if (is_dir($folderPath)) {
							$files = glob($folderPath.'/*'); // get all file names
							$this->delete_files($files,$insertedId);
							//rmdir(public_path('template_data') . DIRECTORY_SEPARATOR . $insertedId);
					}
					if (mkdir($folderPath, 0777)) {
					return Redirect::to("upload-template-files/$insertedId");
					}                 
                }
			  }// try block end
			  catch (\Aws\S3\Exception\S3Exception $e)  {
				$message = $e->getMessage();
				$errorMessage = 'There is some error while creating crm template. Please try again later!';
				flash($errorMessage, "danger");
				return redirect('/list-crm-templates');
			 }
            }
            else{
                $message = "Template with '$awsName' already exist in system!";
                flash($message, 'danger');
                return Redirect::to('add-template');
            }
        }else{
            $bucketRegions = BucketRegions::get();
            return view('adminsOnly.templates.addTemplates', compact('bucketRegions'));
        }
    }

    /*
     * function to delete Templates
     * created by BK
     * created on 19th June
     */
    public function deleteTemplate($templateID)
    {
        //get template data
        $templateData 	= BucketTemplates::where('id', "=", $templateID)->first();
        $templateName 	= $templateData->aws_name;
        $templateRegion = $templateData->template_region;

        //get region code from - required       
        $regionCode = $templateRegion;
        //create object for "S3Client"
        $primary                = "yes";
        $bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => $regionCode,
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        try {
            //get list of all buckets and check if bucket name already exist
            $existName = false;
            $contents = $s3client->listBuckets();
            foreach ($contents['Buckets'] as $bucketDetails) {
                if ($templateName == $bucketDetails['Name']) {
                    $existName = true;
                }
            }
            //if name already exist, then return error message
            if ($existName) {
                $cont = $s3client->getIterator('ListObjects', array('Bucket' => $templateName));
                foreach ($cont as $fileName){
                    $fName = $fileName['Key'];
                    $result = $s3client->deleteObject(array(
                        'Bucket' => $templateName,
                        'Key'    => $fName
                    ));
                }
                $s3client->deleteBucket(array(
                    'Bucket' => $templateName
                ));

                $whereArray = array('template_id'=>$templateID);
                //delete template from DB
                BucketTemplates::findOrFail($templateID)->delete();
                //delete files from DB
                TemplateFiles::where($whereArray)->delete();
                //delete folder entries from from DB
                TemplateFolders::where($whereArray)->delete();

                flash("$templateName deleted successfully!");
                return redirect('/list-crm-templates');
            }
            else {
                $whereArray = array('template_id'=>$templateID);
                //delete template from DB
                BucketTemplates::findOrFail($templateID)->delete();
                //delete files from DB
                TemplateFiles::where($whereArray)->delete();
                //delete folder entries from from DB
                TemplateFolders::where($whereArray)->delete();

                flash("$templateName deleted successfully!");
                return redirect('/list-crm-templates');
            }
        } catch (S3Exception $e) {
            $return = array(
                'type' => 'error',
                'message' => $e->getMessage(),
            );
            return redirect('/list-crm-templates');
        }
    }

    /*
     * function to list TEMPLATES
     * created by BK
     * created on 6th June'17
     */
    public function listTemplates()
    {
        $primary                = "yes";
        $bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();
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
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        // Using operation methods creates command implicitly.
        $contents = $s3client->listBuckets();
        return view('adminsOnly.templates.viewTemplates', compact('contents', 's3client'));
    }

    /*
     * function to list TEMPLATES
     * created by BK
     * created on 6th June'17
     * change by n.k on 6th july
     * add list of server
     */
    public function listCrmTemplates()
    {
        $primary                =   "yes";
        $bucketAuthCredentials  =   ConfigAuth::where('primary_network', "=", $primary)->first();
        $activeConfigId 		=   $bucketAuthCredentials['id'];
        $bucketKey              =   $bucketAuthCredentials['key'];
        $bucketSecret           =   $bucketAuthCredentials['secret'];
        $templatesArr  			=   BucketTemplates::where('aws_server_id', "=", $activeConfigId)->get();
        foreach($templatesArr as $templateVal)
        {
            $templateId     =  $templateVal['id'];
            $BucketName     =  $templateVal['aws_name'];
            $regionCode     =  $templateVal['template_region'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $regionCode,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //$BucketName = 'www.support.microsoft9002yfrmsrbchs9696.com';
            $result = json_encode($s3client->doesBucketExist($BucketName));
            if($result==='false')
            {
                $whereArray = array('template_id'=>$templateId);
                //delete template from DB
                BucketTemplates::where('id',$templateId)->delete();
                //delete files from DB
                TemplateFiles::where($whereArray)->delete();
                //delete folder entries from from DB
                TemplateFolders::where($whereArray)->delete();
            }
        }
        $templates  			=   BucketTemplates::where('aws_server_id', "=", $activeConfigId)->get();
        $status        		    =   "Inactive";
        $allAwsServer   		=   ConfigAuth::where('status', "=", $status)->orderBy('id','desc')->get();
        return view('adminsOnly.templates.viewCrmTemplates', compact('templates','allAwsServer'));
    }

    /*
     * function to manage second step of TEMPLATE - UPLOAD FILES
     * created by BK
     * created on 9th June'17
     */
    public function uploadTemplateFiles($templateId, $folderIN= null)
    {
        if(!empty($folderIN)){
            $getCurrentFolderName = explode('/',$folderIN);
            $folderName = end($getCurrentFolderName);
			try {
//				$contents = $s3client->listBuckets();
            //get folder name
            $folderNameDetails = TemplateFolders::where('template_id', "=", $templateId)->where('folder_name', '=', $folderName)->first();
            $folderID = $folderNameDetails['id'];
            $parentFolder = $folderNameDetails['parent_folder'];
            //if in folder, then get files of folder
            $templateFiles = TemplateFiles::where('template_id', "=", $templateId)->where('folder_id', '=', $folderID)->get();
            $templateFolders = TemplateFolders::where('template_id', "=", $templateId)->where('parent_folder', '!=', 0)->where('parent_folder', '=', $folderID)->where('folder_name', '!=', $folderName)->get();
            }
			catch (\Aws\S3\Exception\S3Exception $e)  {
				$message = $e->getMessage();
				$errorMessage = 'There is some error while updating bucket field. Please try again later!';
				flash($errorMessage, "danger");
				return redirect('/list-crm-templates');
			}
		}else{
            $folderID = '';
            $folderName = '';
            //if at root, then show files and folder for the same
            $templateFiles = TemplateFiles::where('template_id', "=", $templateId)->where('folder_id', '=', 0)->get();
            $templateFolders = TemplateFolders::where('template_id', "=", $templateId)->where('parent_folder', '=', 0)->get();
        }
        return view('adminsOnly.templates.uploadTemplates', compact('templateId', 'folderIN', 'folderID', 'folderName', 'templateFiles', 'templateFolders'));
    }

    /*
    * function to create folder
    * created by BK
    * created on 7th June'17
    */
    public function addFolder()
    {
        if(!empty($_POST['folder_name']) && isset($_POST['parent_folder'])){
            $templateID = $_POST['template_id'];
            $folderName = $_POST['folder_name'];
            $parentFolder = (!empty($_POST['parent_folder'])) ? $_POST['parent_folder'] : 0;
            $parentFolderName = $_POST['parent_folder_name'];

            //get template data
            $templateData = BucketTemplates::where('id', "=", $templateID)->first();
            $templateName = $templateData->aws_name;
            $templateRegion = $templateData->template_region;

            //get region code from - required
            //$regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
            //$templateRegion = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            //folder path
            $folderPath =  (!empty($parentFolderName)) ? public_path('template_data').DIRECTORY_SEPARATOR.$parentFolderName.DIRECTORY_SEPARATOR.$folderName :
                public_path('template_data').DIRECTORY_SEPARATOR.$templateID.DIRECTORY_SEPARATOR.$folderName;

            //AWS folder path
            $awsFolderPath = (!empty($parentFolderName)) ? $parentFolderName.DIRECTORY_SEPARATOR.$folderName.DIRECTORY_SEPARATOR :$folderName.DIRECTORY_SEPARATOR;

            //create object for "S3Client"
            $primary 				 = "yes";
            $bucketAuthCredentials   = ConfigAuth::where('primary_network', "=", $primary)->first();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $templateRegion,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //add condition try catch
            try {
                // Upload data.
                $result = $s3client->putObject(array(
                    'Bucket'       => $templateName,
                    'Key'          => $awsFolderPath,
                    'SourceFile'   => '/',
                    'ContentType'  => 'text/html',
                    'ACL'          => 'public-read',
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ));
                //check if folder created or not
                if($result['ObjectURL']){
                    if(!is_dir($folderPath)){
                        if(mkdir($folderPath, 0777)){
                            //create structure in folder DB
                            $addFolder             = new TemplateFolders();
                            $addFolder->template_id  = $templateID;
                            $addFolder->folder_name = $folderName;
                            $addFolder->parent_folder  = $parentFolder;
                            $addFolder->save();
                            flash('Folder created successfully!');
                            $message = "Folder successfully created!";
                            //return response
                            $return = array(
                                'type' => 'success',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }else{
                            $message = "Please try again later!";
                            //return response
                            $return = array(
                                'type' => 'error',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }
                    }else{
                        $message = "'$folderName' already exist in the directory!";
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => $message,
                        );
                        return json_encode($return);
                    }
                }else{
                    $message = "There is some error while creating directory, please try again later!";
                    //return response
                    $return = array(
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            } catch (S3Exception $e) {
                $return = array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                );
                return json_encode($return);
            }
        }
    }

    /*
    * function to add files in selected folder
    * created by BK
    * created on 7th June'17
    */
    public function addFiles()
    {
        if(!empty($_POST)){
            $templateID = $_POST['template_id'];
            $parentFolder = (!empty($_POST['parent_folder'])) ? $_POST['parent_folder'] : 0;
            $parentFolderName = $_POST['parent_folder_name'];
            $uploadFilePath = $_POST['upload_file_path'];

            //get template data
            $templateData = BucketTemplates::where('id', "=", $templateID)->first();
            $templateName = $templateData->aws_name;
            $templateRegion = $templateData->template_region;
			
            //get region code from - required
           // $regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
           // $templateRegion = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            //upload file name
            $uploadFolderPath = public_path('template_data').DIRECTORY_SEPARATOR.$templateID.DIRECTORY_SEPARATOR.$uploadFilePath;

            //create AWS path
            $awsFolderPath = (!empty($_POST['parent_folder'])) ? $uploadFilePath.DIRECTORY_SEPARATOR : '';

            //create object for "S3Client"
            $primary = "yes";
            $bucketAuthCredentials   = ConfigAuth::where('primary_network', "=", $primary)->first();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $templateRegion,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);

            foreach ($_FILES["templateFiles"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["templateFiles"]["tmp_name"][$key];
                    $name = basename($_FILES["templateFiles"]["name"][$key]);
                    $contentType = $_FILES["templateFiles"]["type"][$key];
                    try {
                        // Upload data.
                        $result = $s3client->putObject(array(
                            'Bucket'       => $templateName,
                            'Key'          => $awsFolderPath.$name,
                            'SourceFile'   => $tmp_name,
                            'ContentType'  => $contentType,
                            'ACL'          => 'public-read',
                            'StorageClass' => 'REDUCED_REDUNDANCY',
                        ));
                        //check if folder created or not
                        if($result['ObjectURL']){
                            if(file_exists("$uploadFolderPath/$name")) {
                                move_uploaded_file($tmp_name, "$uploadFolderPath/$name");
                                flash('Files uploaded successfully!');
                            }else{
                                if(move_uploaded_file($tmp_name, "$uploadFolderPath/$name")){
                                    //create structure in folder DB
                                    $addFiles             = new TemplateFiles();
                                    $addFiles->template_id  = $templateID;
                                    $addFiles->folder_id  = $parentFolder;
                                    $addFiles->file_name  = $_FILES["templateFiles"]["name"][$key];
                                    $addFiles->file_path  = (!empty($uploadFilePath)) ? $uploadFilePath.DIRECTORY_SEPARATOR.$_FILES["templateFiles"]["name"][$key] : $_FILES["templateFiles"]["name"][$key];
                                    $addFiles->save();
                                    flash('Files uploaded successfully!');
                                }
                            }
                        }else{
                            flash('There is some error while uploading, please try again later!', 'danger');
                        }
                    } catch (S3Exception $e) {
                        echo $e->getMessage() . "\n";
                        flash($e->getMessage());
                    }
                }
            }
            $redirectUrl = "upload-template-files/$templateID/$uploadFilePath";
            return Redirect::to($redirectUrl);
        }
    }

    /*
   * function to make tempalte duplicate
   * created by NK
   * created on 6 July'17
   */
    public function moveTemplateToNewAws()
    {
        $duplciateFrom                  = Input::get('duplicate_for');
        $newBucketName                  = Input::get('new_bucket_name');
        $region                         = Input::get('duplicateToAwsRegion');
        $template_id                    = Input::get('template_id');
        $status                         = "Active";
        $primary                		= "yes";
        //$awsServerActive       		=	 ConfigAuth::where('status', "=", $status)->first();
        $awsServerActive        		= ConfigAuth::where('primary_network', "=", $primary)->first();
        $activeServerKey        		= $awsServerActive['key'];
        $actvieServerSecretKey  		= $awsServerActive['secret'];

        $activeServerKey                = $awsServerActive['key'];
        $actvieServerSecretKey          = $awsServerActive['secret'];
        $copyToServerId                 = Input::get('aws_server_id');
        $allAwsServer                   = ConfigAuth::where('id', "=", $copyToServerId)->first();
        $toServerKey                    = $allAwsServer['key'];
        $toServerSecretKey              = $allAwsServer['secret'];
        $toServerName                   = $allAwsServer['aws_name'];
        $bucket 		                = $duplciateFrom;

        //create object for "S3Client"
        $s3clientActive               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $activeServerKey,
                'secret' => $actvieServerSecretKey
            ]
        ]);
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);
        if($newBucketName=="")
        {
            $newBucketName = $duplciateFrom;
        }
        //create string policy for Bucket
        $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';

        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3clientToMove->listBuckets();
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
                'value' => '2',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
        else {
            //check index.html file for existing bucket
            $existIndex = false;
            $existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
            foreach ($existingBucket['Contents'] as $existFiles) {
                if ($existFiles['Key'] == 'index.html') {
                    $existIndex = true;
                } else {
                    $existIndex = false;
                }
            }
            //if index file exist, then create bucket
            if($existIndex)
            {
                try{
                    //create instance of NEW server, where we have to move/copy
                    $result3 = $s3clientToMove->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    //list the current bucket from active AWS server, from where we have to move/copy
                    $stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to
                    foreach ($stp['Contents'] as $object) {
                        //create instance of NEW server, where we have to move/copy
                        $s3clientToMove->copyObject(array(
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
                    //create instance of NEW server, where we have to move/copy
                    $result2 = $s3clientToMove->putBucketWebsite($arg);
                    $result3 = $s3clientToMove->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    //create instance of NEW server, where we have to move/copy
                    $location = $s3clientToMove->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    //response in case of success if counter match!
                    $finalMessage =  $newBucketUrl.' template successfully created on new server'.$toServerName;

                    /* new */
                    $templateData                   = BucketTemplates::where('id', "=", $template_id)->first();
                    $templateName                   = $templateData['template_name'];
                    $templateRegion                 = $templateData['template_region'];
                    $templateObj                    = new BucketTemplates();
                    $templateObj->aws_server_id     = $copyToServerId;
                    $templateObj->template_name     = $templateName;
                    $templateObj->template_region   = $templateRegion;
                    $templateObj->aws_name          = $newBucketName;
                    $templateObj->save();
                    $insertedTemplateId             = $templateObj->id;
                    $folderDataCount                = TemplateFolders::where('id',$template_id)->count();
                    if($folderDataCount>0)
                    {
                        $folderData                     = TemplateFolders::where('id', $template_id)->get();
                        foreach ($folderData as $folderVal)
                        {
                            $addFolder = new TemplateFolders();
                            $addFolder->template_id     = $insertedTemplateId;
                            $addFolder->folder_name     = $folderVal->folder_name;
                            $addFolder->parent_folder   = $folderVal->parent_folder;
                            $addFolder->save();
                            $folderId  = $addFolder->id;
                            $templateFilesData          = TemplateFiles::where('folder_id',$folderId)->get();
                            foreach($templateFilesData as $templateFilesVal)
                            {
                                $addFiles               = new TemplateFiles();
                                $addFiles->template_id  = $insertedTemplateId;
                                $addFiles->folder_id    = $addFolder->id;
                                $addFiles->file_name    = $templateFilesVal->id;
                                $addFiles->file_path    = $templateFilesVal->id;
                                $addFiles->save();
                            }
                        }
                    }
                    $templateFilesDataCount    = TemplateFiles::where('id',$template_id)->where('folder_id',0)->count();
                    if($templateFilesDataCount>0)
                    {
                        $templateFilesData    = TemplateFiles::where('id',$template_id)->where('folder_id',0)->count();
                        foreach ($templateFilesData as $templateFilesVal)
                        {
                            $addFiles = new TemplateFiles();
                            $addFiles->template_id = $insertedTemplateId;
                            $addFiles->folder_id = 0;
                            $addFiles->file_name = $templateFilesVal->id;
                            $addFiles->file_path = $templateFilesVal->id;
                            $addFiles->save();
                        }
                    }
                    $srcPath = public_path('template_data').DIRECTORY_SEPARATOR.$template_id;
                    $destPath = public_path('template_data').DIRECTORY_SEPARATOR.$insertedTemplateId;
                    $this->recurse_copy($srcPath,$destPath);
                    /* new */
                    flash($finalMessage);
                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'message' => $finalMessage,
                        'b_url' => $newBucketUrl,
                        'b_name'=>$newBucketName,
                    );
                    return json_encode($return);
                }
                catch(\Exception $exception){
                    $xmlResposne = $exception->getAwsErrorCode();
                    if($xmlResposne=="BucketAlreadyExists")
                    {
                        $message = "Bucket already exists. Please change the URL.";
                    }
                    else
                    {
                        $message = $xmlResposne;
                    }
                    $return = array(
                        'value' => '2',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            }
        }
    }
}
