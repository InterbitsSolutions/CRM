<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
// Home and Update Password Routes
Route::get('/', 'PagesController@index');
Route::get('/update-password', 'PagesController@showUpdatePasswordForm');
Route::put('/update-password', 'PagesController@updatePassword');

// Clients Routes
Route::resource('/clients', 'ClientsController');

Route::resource('/buckets', 'BucketsController');
Route::resource('/buckets', 'BucketsController');

// Invoice Routes
Route::resource('/clients.invoices', 'InvoicesController', ['except' => [
    'update', 'edit'
]]);

// Project Routes
Route::resource('/clients.projects', 'ProjectsController', ['except' => [
    'update', 'edit'
]]);
// Admin Routes
Route::resource('/admins', 'AdminsController', ['except' => [
    'show'
]]);

// Authentication Routes
Auth::routes();

// Routes for logged in clients only
Route::get('/invoices', 'ClientsOnlyController@allInvoices');
Route::get('/invoices/{id}', 'ClientsOnlyController@showInvoice');
Route::get('/invoices/{id}/pay', 'ClientsOnlyController@payInvoice');
Route::post('/invoices/{id}', 'ClientsOnlyController@paidInvoice');

Route::get('/projects', 'ClientsOnlyController@allProjects');
Route::get('/projects/{id}', 'ClientsOnlyController@showProject');
Route::get('/projects/{id}/accept', 'ClientsOnlyController@acceptProject');

//test html
Route::get('/test-html', 'Admin\TeshtmlController@test_html')->name('test_html');

//route to manage fraud IP for the Customers
Route::get('fraud/customerip', 'Admin\FraudController@customer_ip')->name('customer_ip');
Route::get('fraud/analytic-id/{mid}', 'Admin\FraudController@getAnalyticsId')->name('analytic-id');
Route::get('fraud/ringba-code/{mid}', 'Admin\FraudController@ringbaCode')->name('ringba-code');

//template bucket module
Route::get('/add-template', 'TemplatesController@addTemplate');
Route::post('/add-template', 'TemplatesController@addTemplate');
Route::get('/delete-template/{id}', 'TemplatesController@deleteTemplate');
Route::get('/list-templates', 'TemplatesController@listTemplates');
Route::get('/list-crm-templates', 'TemplatesController@listCrmTemplates');
Route::get('/upload-template-files/{id}', 'TemplatesController@uploadTemplateFiles');
Route::get('/upload-template-files/{id}/{folderName?}', 'TemplatesController@uploadTemplateFiles')->where('folderName', '(.*)');

//master bucket module
Route::get('/list-master-buckets', 'MasterBucketsController@listMasterBuckets');
//copy
Route::post('/copy-master-bucket', 'MasterBucketsController@copyMasterBucket');
//add
Route::get('/edit-master-bucket/{id}', 'MasterBucketsController@editMasterBucket');
Route::post('/edit-master-bucket/{id}', 'MasterBucketsController@editMasterBucket');
//edit
Route::get('/add-master-bucket', 'MasterBucketsController@addMasterBucket');
Route::post('/add-master-bucket', 'MasterBucketsController@addMasterBucket');
//delete
Route::get('/delete-master-bucket/{id}', 'MasterBucketsController@deleteMasterBucket');
//upload
Route::get('/upload-master-files/{id}', 'MasterBucketsController@uploadMasterFiles');
Route::get('/upload-master-files/{id}/{folderName?}', 'MasterBucketsController@uploadMasterFiles')->where('folderName', '(.*)');

//add file/folder
Route::post('/add-folder', 'TemplatesController@addFolder');
Route::post('/add-files', 'TemplatesController@addFiles');

//child bucket module
Route::post('/create-child-bucket', 'BucketsController@createChildBucket'); // use to create bucket from master bucket

//duplicate buckets
Route::post('/duplicate-bucket', 'BucketsController@duplicator');
Route::post('/delete-bucket', 'BucketsController@deleteBucket');
Route::post('/delete-multiple-bucket', 'BucketsController@deleteMultipleBuckets');
Route::get('/duplicate-list-buckets', 'BucketsController@duplicateListBuckets');

//duplicate buckets to Aws
Route::get('/duplicate-bucket-to-aws', 'BucketsController@duplicateToAws');
//Route::get('/move-tempalte-to-new-aws', 'BucketsController@moveTemplateToNewAws');
Route::get('/move-tempalte-to-new-aws', 'TemplatesController@moveTemplateToNewAws');

//route to manage configurations
Route::get('/add-auth', 'ConfigAuthController@addConfig');
Route::post('/add-auth', 'ConfigAuthController@addConfig');
Route::get('/list-config', 'ConfigAuthController@listConfig');
Route::get('/config/{id}/edit', 'ConfigAuthController@editConfig');
Route::post('/config/{id}/', 'ConfigAuthController@editConfig');
Route::get('/config/{id}/{status}', 'ConfigAuthController@updateStatus');
Route::get('/activateConfig/{id}', 'ConfigAuthController@activateConfig');
Route::get('/primary/{id}/{status}', 'ConfigAuthController@updatePrimaryNetwork');

//route for dummy buckets
Route::resource('/dummy-buckets', 'DummyBucketsController');
Route::post('/duplicate-dummybucket', 'DummyBucketsController@duplicator');
Route::post('/delete-dummybucket', 'DummyBucketsController@deleteBucket');

//test link
Route::get('/testlink', 'BucketsController@testLink');

//PID phone
Route::get('/get-pid-phone', 'Admin\FraudController@getPidPhone');
Route::get('/save-bucket-params', 'Admin\FraudController@saveParams');

//manage bucket fields
Route::get('/manage-bucket-fields', 'BucketFieldsController@manageBucketFields');
Route::get('/manage-bucket-fields/{fieldType}', 'BucketFieldsController@manageBucketFields');
Route::get('/edit-field/{fieldType}/{id}', 'BucketFieldsController@editField');
Route::post('/edit-field/{fieldType}/{id}', 'BucketFieldsController@editField');
Route::get('/add-field/{fieldType}', 'BucketFieldsController@addField');
Route::post('/add-field/{fieldType}', 'BucketFieldsController@addField');
Route::get('/delete-field/{fieldType}/{id}', 'BucketFieldsController@deleteField');

//Multiple buckets
Route::get('/multiple-buckets', 'MultipleBucketsController@index');
Route::post('/multiple-buckets', 'MultipleBucketsController@index');
Route::post('/multiple-duplicate-bucket', 'MultipleBucketsController@duplicator');
Route::post('/multiple-delete-bucket', 'MultipleBucketsController@deleteBucket');
Route::post('/multiple-bulk-delete', 'MultipleBucketsController@deleteMultipleBuckets');

//logout call
Route::get('/logout', 'Auth\LoginController@logout');

//Bucket params feature
Route::get('/list-bucket-params', 'BucketParamsController@listBucketParams');
Route::get('/add-bucket-params', 'BucketParamsController@addBucketParams');
Route::post('/add-bucket-params', 'BucketParamsController@addBucketParams');
Route::get('/edit-bucket-params/{id}', 'BucketParamsController@editBucketParams');
Route::post('/edit-bucket-params/{id}', 'BucketParamsController@editBucketParams');
Route::get('/delete-bucket-params/{id}', 'BucketParamsController@deleteBucketParams');

//Multiple buckets
Route::get('/test-buckets', 'BucketsController@testBuckets');
Route::get('/google-buckets', 'BucketsController@googleBuckets');

//Test links
Route::get('/aws-buckets', 'BucketTestController@awsBuckets');
Route::get('/update-phone-xml-fie', 'BucketsController@update_phone_xml_fie');
Route::get('/update-phone-xml-fie', 'BucketsController@update_phone_xml_fie');

//bucket backup
Route::get('/export-buckets', 'BucketBackupController@index');
Route::post('/create-backup', 'BucketBackupController@bucketBackup');
Route::get('/backup-complete', 'BucketBackupController@backupComplete')->name('backup-complete');
Route::get('/import-buckets', 'BucketBackupController@importBuckets');
Route::post('/import-buckets', 'BucketBackupController@importBuckets');
Route::get('/upload-buckets', 'BucketBackupController@uploadBuckets');
Route::post('/upload-buckets', 'BucketBackupController@uploadBuckets');


//crmbackup(bbsr)
Route::get('/download-dir/{filename}', 'BackupsController@downloadDirectory');
Route::get('/capture-bucket-lead-info/{mid}/{bname}', 'Admin\FraudController@capture_bucket_lead_info');
Route::get('/script-backup', 'BackupsController@backup');
Route::post('/crmbackup', 'BackupsController@crmbackup');
Route::get('/deletezip/{filename}', 'BackupsController@deletezip');
Route::post('/delete-multiple-files', 'BackupsController@deleteMultipleFiles');
Route::get('/zipBackup/', 'BackupsController@zipBackup');
Route::post('/zipBackup/', 'BackupsController@zipBackup');


//network hit lists
Route::get('/network-hit-list', 'NetworkHitController@listNetworkHits');


Route::get('/web-analytics', 'Admin\FraudController@webAnalytics');


//Users
Route::get('/add-users', 'UsersController@addUser');
Route::post('/add-users', 'UsersController@addUser');
Route::get('/edit-user/{id}', 'UsersController@editUser');
Route::post('/edit-user/{id}', 'UsersController@editUser');
Route::post('/list-users', 'UsersController@listUser');
Route::get('/list-users', 'UsersController@listUser');
Route::get('/delete-user/{id}', 'UsersController@deleteUser');
Route::post('/change-password', 'UsersController@changePassword');

//Roles
Route::get('/edit-user-role/{id}', 'UserRoleController@editRole');
Route::post('/edit-user-role/{id}', 'UserRoleController@editRole');
Route::get('/delete-user-role/{id}', 'UserRoleController@deleteRole');
Route::get('/view-user-role/{id}', 'UserRoleController@viewRole');
Route::get('/add-user-role', 'UserRoleController@addRole');
Route::post('/add-user-role', 'UserRoleController@addRole');
Route::get('/list-user-roles', 'UserRoleController@listRoles');
Route::post('/check-duplicate-role', 'UserRoleController@checkDuplicateRole');
// template Backup
Route::get('/export-template', 'TemplatesBackupController@exportTemplate');
// template Backup
Route::get('/export-template', 'TemplatesBackupController@viewExportTemplate');
Route::post('/export-template', 'TemplatesBackupController@exportTemplate');
Route::get('/export-template-complete', 'TemplatesBackupController@exportTemplateComplete')->name('export-template-complete');
Route::post('/duplicate-bucket-with-custom-counter', 'BucketsController@duplicate_with_custom_counter');
Route::get('/user-log', 'UsersController@userLog');
Route::post('/user-log', 'UsersController@userLog');
Route::get('/deletelog/{id}', 'UsersController@deletelog');
Route::post('/delete-multiple-log', 'UsersController@deleteMultipleLog');

//Dashboard Data
Route::post('/get-dashboard-content', 'PagesController@getDashboardContent');