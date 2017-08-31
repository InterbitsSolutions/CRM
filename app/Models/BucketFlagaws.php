<?php
namespace App\Models;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class BucketFlagaws extends Model
{
    protected $table = "buckets_flag_aws";
    public $timestamps = true;
}