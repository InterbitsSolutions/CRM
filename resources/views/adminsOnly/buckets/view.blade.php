@extends('layouts.dataTable')

@section('page_title', 'View Buckets')

@section('content')
<!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">All Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Buckets</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <div class="table-responsive">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Bucket Name</th>
                                <th>Bucket Region</th>
                                <th>Bucket Short Code</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($buckets as $bucket)
                                <tr>
                                <td>{{ $bucket->id }}</td>
                                <td>{{ $bucket->bucket_name }}</td>
                                <td>{{ (!empty($bucket->bucket_region)) ? $bucket->bucket_region : "-" }}</td>
                                <td>{{ (!empty($bucket->bucket_short_code)) ? $bucket->bucket_short_code : "-"  }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
@endsection