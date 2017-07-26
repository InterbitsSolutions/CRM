@extends('layouts.dataTable')

@section('page_title', 'Buckets')

@section('content')
    <!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Manage Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Buckets</li>
                </ol>
            </div>
            <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                <div class="pull-right"><h2 class="bucket-total"><p> {{ $url }}</p></h2></div>
            </div>
        </div>
        <!-- /row -->
     

    <!-- copy to aws popup -->
	
	
        <!-- /.container-fluid -->
    
     
<?php
    function getNumericVal ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return $matches[0][0];
    }
?>
@endsection