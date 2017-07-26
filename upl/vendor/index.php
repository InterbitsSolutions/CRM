<?php
   // Include the SDK using the Composer autoloader
     require 'vendor/autoload.php';
  $s3 = Aws\S3\S3Client::factory();
  
  echo $s3;
  
  ?>
