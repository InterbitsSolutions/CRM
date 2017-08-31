@extends('layouts.dataTable')

@section('page_title', 'Bucket Parameters')

@section('content')
<!-- Page Content -->
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Bucket Parameters</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">Bucket Parameters</li>
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
                                <th>Bucket Region</th>
                                <th>Bucket Short Code</th>
                                <th>Parameter String</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            @foreach($bucketParams as $bucket)
                            <tr>
                                <td>{{ $counter }}</td>
                                <td>{{ (!empty($bucket->bucket_region)) ? strtoupper($bucket->bucket_region) : "-" }}</td>
                                <td>{{ (!empty($bucket->bucket_short_code)) ? strtoupper($bucket->bucket_short_code) : "-"  }}</td>
                                <td>{{ (!empty($bucket->bucket_parameters)) ? $bucket->bucket_parameters : "-"  }}</td>
                                <td class="record_actions">
                                    <a href="{{ url('/edit-bucket-params/'.$bucket->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                    <a href="{{ url('/delete-bucket-params/'.$bucket->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $bucket->bucket_name }} ?')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php $counter++; ?>
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