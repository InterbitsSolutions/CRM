@extends('layouts.dataTable')

@section('page_title', 'Test Link')

@section('content')
  <!-- Page Content -->
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Test</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="#">Dashboard</a></li>
        </ol>
      </div>
    </div>
    <!-- /row -->
    <div class="row">
      <div class="col-sm-12">
        <div class="white-box">
          PID: {{$pid}}
        </div>
      </div>
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->
  </div>
@endsection