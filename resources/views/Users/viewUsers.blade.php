@extends('layouts.dataTable')

@section('page_title', 'Users')

@section('content')
<!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">View User</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">View User</li>
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1;?>
                            @foreach($users as $user)
                                <tr>
                                <td>{{ $counter }}</td>
                                <td>{{ (!empty($user->name)) ? $user->name : "-" }}</td>
                                <td>{{ (!empty($user->email)) ? $user->email : "-"  }}</td>
                                <td>{{ (!empty($user->role)) ? $roles[$user->role] : "-"  }}</td>
                                <td class="record_actions">
                                    <a href="{{ url('/edit-user/'.$user->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                    <a href="{{ url('/delete-user/'.$user->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $user->main_url }} ?')"><i class="fa fa-trash"></i></a>
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
