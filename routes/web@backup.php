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
Route::get('/test-html', 'Admin\TeshtmlController@test_html')->name('test_html');;

//route to manage fraud IP for the Customers
Route::get('fraud/customerip', 'Admin\FraudController@customer_ip')->name('customer_ip');

//route to add buckets
Route::get('/manage-buckets', 'BucketTestController@manageBuckets');
Route::post('/add-bucket', 'BucketTestController@addBucket');
Route::get('/list-buckets', 'BucketTestController@listBuckets');

//template bucket module
Route::get('/add-template', 'BucketTestController@addTemplate');
Route::post('/add-template', 'BucketTestController@addTemplate');
Route::get('/list-templates', 'BucketTestController@listTemplates');
Route::get('/list-crm-templates', 'BucketTestController@listCrmTemplates');
Route::get('/upload-template-files/{id}', 'BucketTestController@uploadTemplateFiles');
Route::get('/upload-template-files/{id}/{folderName?}', 'BucketTestController@uploadTemplateFiles')->where('folderName', '(.*)');

//master bucket module
Route::get('/list-master-buckets', 'BucketTestController@listMasterBuckets');
//add
Route::get('/edit-master-bucket/{id}', 'BucketTestController@editMasterBucket');
Route::post('/edit-master-bucket/{id}', 'BucketTestController@editMasterBucket');
//edit
Route::get('/add-master-bucket', 'BucketTestController@addMasterBucket');
Route::post('/add-master-bucket', 'BucketTestController@addMasterBucket');
//delete
Route::get('/delete-master-bucket/{id}', 'BucketTestController@deleteMasterBucket');
//upload
Route::get('/upload-master-files/{id}', 'BucketTestController@uploadMasterFiles');
Route::get('/upload-master-files/{id}/{folderName?}', 'BucketTestController@uploadMasterFiles')->where('folderName', '(.*)');
//add file/folder
Route::post('/add-folder', 'BucketTestController@addFolder');
Route::post('/add-files', 'BucketTestController@addFiles');

//child bucket module
Route::post('/create-child-bucket', 'BucketTestController@createChildBucket'); // use to create bucket from master bucket
Route::get('/list-child-buckets', 'BucketTestController@listChildBuckets');
Route::get('/upload-child-files/{id}', 'BucketTestController@uploadChildFiles');
Route::get('/upload-child-files/{id}/{folderName?}', 'BucketTestController@uploadChildFiles')->where('folderName', '(.*)');
Route::get('/delete-child-bucket/{id}', 'BucketTestController@deleteChildBucket');

//duplicate buckets
Route::post('/duplicate-bucket', 'BucketsController@duplicator');
Route::post('/delete-bucket', 'BucketsController@deleteBucket');
Route::post('/delete-multiple-bucket', 'BucketsController@deleteMultipleBuckets');
Route::get('/duplicate-list-buckets', 'BucketTestController@duplicateListBuckets');

//route to manage configurations
Route::get('/add-auth', 'ConfigAuthController@addConfig');
Route::post('/add-auth', 'ConfigAuthController@addConfig');
Route::get('/list-config', 'ConfigAuthController@listConfig');
Route::get('/config/{id}/edit', 'ConfigAuthController@editConfig');
Route::post('/config/{id}/', 'ConfigAuthController@editConfig');
Route::get('/config/{id}/{status}', 'ConfigAuthController@updateStatus');

//route for dummy buckets
Route::resource('/dummy-buckets', 'DummyBucketsController');
Route::post('/duplicate-dummybucket', 'DummyBucketsController@duplicator');
Route::post('/delete-dummybucket', 'DummyBucketsController@deleteBucket');

//test link
Route::get('/testlink', 'BucketTestController@testLink');

//manage bucket fields
Route::get('/manage-bucket-fields', 'BucketFieldsController@manageBucketFields');
Route::get('/manage-bucket-fields/{fieldType}', 'BucketFieldsController@manageBucketFields');
Route::get('/edit-field/{fieldType}/{id}', 'BucketFieldsController@editField');
Route::post('/edit-field/{fieldType}/{id}', 'BucketFieldsController@editField');
Route::get('/add-field/{fieldType}', 'BucketFieldsController@addField');
Route::post('/add-field/{fieldType}', 'BucketFieldsController@addField');
Route::get('/delete-field/{fieldType}/{id}', 'BucketFieldsController@deleteField');