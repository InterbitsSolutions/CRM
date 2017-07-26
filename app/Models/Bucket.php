<?php

namespace App\Models;


use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

/*$bucketName='testorsss';

$s3 = S3Client::factory();

$storage = Storage::createS3Driver([
    'driver' => 's3',
    'key'    => 'AKIAJLV6DIJLVNQFOYNA',
    'secret' => '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',
    'region' => 'us-west-2',
    'bucket' => $bucketName,
]);
*/

//$contents = $storage->getBucket("testorsss");

class Bucket extends Model
{
     
	public $str="hello world";
	
   
	
	public function bucketlist()
    {
			
		
		$stp = "hello world";
		
        return $stp;
    }

  
}
