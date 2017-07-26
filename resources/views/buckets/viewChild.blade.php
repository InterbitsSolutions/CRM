@extends('layouts.dataTable')

@section('page_title', 'Buckets')

@section('content')
<!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Buckets</li>
                </ol>
            </div>
            <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                <div class="pull-right"><h2 class="bucket-total">{{ count($buckets) }}<p> Buckets</p></h2></div>
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
                                <th>Bucket Link</th>
                                <th>Bucket Region</th>
                                <th>Bucket Short Code</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1;?>
                            @foreach($buckets as $bucket)
                                <tr>
                                <td>{{ $counter }}</td>
                                <td>{{ $bucket->bucket_name }}</td>
                                <td>{{ $bucket->bucket_link }}</td>
                                <td>{{ (!empty($bucket->bucket_region)) ? $bucket->bucket_region : "-" }}</td>
                                <td>{{ (!empty($bucket->bucket_short_code)) ? $bucket->bucket_short_code : "-"  }}</td>
                                <td class="record_actions">
                                    <a href="{{ url('/upload-child-files/'.$bucket->id) }}" title="edit" class="btn btn-primary"><i class="fa fa-edit"></i></a>
                                    <a href="{{ url('/delete-child-bucket/'.$bucket->id) }}" title="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete {{ $bucket->bucket_name }} ?')"><i class="fa fa-trash"></i></a>
                                </td>
                                </tr>
                            <?php $counter++;?>
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