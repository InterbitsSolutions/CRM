@extends('layouts.adminDashboard')
@section('page_title', 'Import Buckets')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Import Buckets</h4>
            </div>
			<div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Import Buckets</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title">Basic Information</h3>
                    <h5 style="color:red;">NOTE: Please upload ZIP file for a Signle network at a time in which you want to import the buckets!!</h5>
                    <form id="importFormBuckets" action="{{ url('/import-buckets') }}" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <div>
                                <div class="container col-sm-12">
                                    <!-- The file upload form used as target for the file upload widget -->
                                    {{--<form id="fileupload" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">--}}
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="is_import" id="isImport" value="no">
                                        <input type="file" name="files" id="files">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12" for="example-text">AWS Server</label>
                            <div>
                                <select name="aws_server" id="aws_server" class="form-control" required="">
                                    {{--<option value="">Please select aws server</option>--}}
                                    @foreach($configAuth as $awsDetail)
                                        <option value="{{ $awsDetail->id }}">{{ $awsDetail->aws_name }}</option>
                                    @endforeach
                                </select>
                                <span></span>
                            </div>
                        </div>
                        <input type="button" name="Import Buckets" onclick="return confirmImport()" value="Import Buckets" class="btn btn-primary">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmImport(){
            var fileVal = $("#files").val();
            if(fileVal == ''){
                alert("Please select a ZIP file to Import Buckets!!");
                return false;
            }else{
                if (confirm('Are you sure to Import buckets for selected AWS network?')){
                    $('#overlay').show();
                    $('#importFormBuckets').submit();
                    return false;
                }else{
                    return false;
                }
            }
        }
    </script>
@endsection