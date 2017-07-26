<?php
// Bucket Name
$bucket="www.navtest.com";

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAIGII4VNYV32XWV6Q');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

  $client = S3Client::factory(
      array(
      'key'    => awsAccessKey,
      'secret' => awsSecretKey
       )
      );
?>
