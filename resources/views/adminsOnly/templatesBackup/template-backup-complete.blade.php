@extends('layouts.adminDashboard')
@section('page_title', 'Template Backup Complete')
@section('content')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Template Backup</h4>
        </div>
    </div>
    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <p><a href="{{ url('templateBackup.zip') }}">Click Here</a> to download template backup.</p>
            </div>
        </div>
    </div>
</div>
@endsection