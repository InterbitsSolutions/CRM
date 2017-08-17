@extends('layouts.dataTable')

@section('page_title', 'Admins')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Manage Roles</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Roles</li>
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
                                <th>Roles</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            
                            $counter = 1;
                            ?>
                            @foreach($userRoles as $role)
                                
                                <tr>
                                    <td>{{ $counter}}</td>
                                    <td>{{ $role->role_name }}</td>                                 
                                    <td class="record_actions">
                                        <a href="{{ url('/view-user-role/'.$role->id) }}" title="view" class="btn btn-primary-btn">View</a>
                                        <a href="{{ url('/edit-user-role/'.$role->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
										<a href="{{ url('/delete-user-role/'.$role->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $role->role_name }} ?')"><i class="fa fa-trash"></i></a>
                                        
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