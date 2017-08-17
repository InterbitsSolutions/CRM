@extends('layouts.dataTable')

@section('page_title', 'Export Buckets')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Export Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Backup Complete</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box clearfix">
                    <p>Back Up has been completed successfully.</p>
                    <p><a href="{{ url('bucketBackup.zip') }}">Click Here</a> to download backup</p>
                 </div>
            </div>
        </div>
</div>
@endsection