@extends('layouts.dataTable')

@section('page_title', 'Admins')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Web Analytics</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Web Analytics</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <iframe src="https://analytics.google.com/analytics/web/#embed/report-home/a98783256w146744538p151533672/" name="iframe_analytics" width="300" height="130"></iframe>
					
					
					
					<br /><br />
					<iframe src="https://analytics.google.com/analytics" name="iframe_a" width="300" height="130"></iframe>
					
					
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>

@endsection