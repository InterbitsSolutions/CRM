<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use App\Models\BucketTemplates;
use App\Models\MasterBucketsCounter;
use App\Models\TemplateFiles;
use App\Models\TemplateFolders;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Aws\S3\S3Client;
use Storage;

class TemplatesBackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
    /*
     * function to display all tempalte
     */
    public function viewExportTemplate()
    {
        $primary                =   "yes";
        $bucketAuthCredentials  =   ConfigAuth::where('primary_network', "=", $primary)->first();
        $activeConfigId 		=   $bucketAuthCredentials['id'];
        $templatesArr 			=   BucketTemplates::where('aws_server_id', "=", $activeConfigId)->get();
        return view('adminsOnly.templatesBackup.index', compact('templatesArr'));
    }

    /*
     * function to display compelte backup page
     */
    public function exportTemplateComplete()
    {
        return view('adminsOnly.templatesBackup.template-backup-complete');
    }
    /*
    * function to create ZIP of Buckets files/folder
    * created by nk
    * created on 1stAug '17
    */
    function zipBackup($realPath){
        $fileName = 'templateBackup.zip';
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
     * function to move directory template
     * created by NK
     * created on 17 august 2017
     */
    public  function copy_directory($source,$destination)
    {
        $directory = opendir($source);
        @mkdir($destination);
        while(false !== ( $file = readdir($directory)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($source . '/' . $file) ) {
                    $this->copy_directory($source . '/' . $file,$destination . '/' . $file);
                }
                else {
                    copy($source . '/' . $file,$destination . '/' . $file);
                }
            }
        }
        closedir($directory);
    }
    /*
   * function to create xml file
   * created by NK
   * created on 19 July'17
   * $phoneNumber : From the master bucket table
   */
    public function create_save_xml_fie($phoneNumber='9780058718')
    {
        $xml            = "<?xml version='1.0' encoding='UTF-8'?><phone>$phoneNumber</phone>";
        $xmlFilePath    = public_path('templateBackup').DIRECTORY_SEPARATOR;
        $file           = fopen($xmlFilePath."phonenumber.xml","w");
        fwrite($file,$xml);
        fclose($file);
    }
    public  function deleteDirTemplateBackup($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDirTemplateBackup($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
    /*
    * function to export/backup template
    * created by NK
    * created on 17 august 2017
    */
    public function exportTemplate()
    {
        $tempalteIdArr          =   explode(",",input::get('template_id'));
        $templatesArr 			=   BucketTemplates::whereIn('id',$tempalteIdArr)->get();
        $rootFolderPath         =   public_path('templateBackup');
        $rootFolderPathZip      =   public_path('templateBackup.zip');
        if (is_dir($rootFolderPath))
        {
            $this->deleteDirTemplateBackup($rootFolderPath);
            if(file_exists($rootFolderPathZip)){
                unlink($rootFolderPathZip);

            }
            mkdir($rootFolderPath, 0777);
        }
        else
        {
            mkdir($rootFolderPath, 0777);
        }
        $xml            = "<?xml version='1.0' encoding='UTF-8'?>";
        $xmlFilePath    = public_path('templateBackup').DIRECTORY_SEPARATOR;
        $file           = fopen($xmlFilePath."templateData.xml","w");
        foreach($templatesArr as $templatesVal)
        {
            $src = public_path('template_data').DIRECTORY_SEPARATOR.$templatesVal['id'];
            $des = $rootFolderPath.DIRECTORY_SEPARATOR.$templatesVal['id']."_".$templatesVal['template_name'];
            $xml.="<templateData>";
            $xml.="<templateId>".$templatesVal['id']."</templateId>";
            $xml.="<templateName>".$templatesVal['template_name']."</templateURL>";
            $xml.="<templateURL>".$templatesVal['template_region']."</templateURL>";
            $xml.="<templateAwsName>".$templatesVal['aws_name']."</templateAwsName>";
            $xml.="</templateData>";
            $this->copy_directory($src,$des);
        }
        fwrite($file,$xml);
        fclose($file);
        $realPath               = public_path('templateBackup');
        $this->zipBackup($realPath);
        flash("Template backup has been created successfully");
        return redirect()->route('export-template-complete');
    }

}
