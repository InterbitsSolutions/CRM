@extends('layouts.adminDashboard')
@section('page_title', 'Manage Configurations')
@section('content')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Edit Role</h4>
        </div>
    </div>
    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title">Basic Information</h3>
                <form class="form-material form-horizontal m-t-30" action="{{ url('/edit-user-role').DIRECTORY_SEPARATOR.$userRoles['id'] }}" method="post" id="role">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label class="col-md-12" for="example-text">Role Name</label>
                        <div class="col-md-12">
                            <input type="text" id="user_role_name" name="role_name" class="form-control" value="{{ $userRoles->role_name }}" placeholder="Role Name" required="" >
                            <input type="hidden" id="hidden_role_name" class="form-control" value="{{ $userRoles->role_name }}" placeholder="Role Name" name="hidden_role_name" required="" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text">Permissions</label>
                        <div class="col-md-12">
                            @foreach($module_arr as $module_key => $module)
                            <input type="checkbox" name="modules[]" value="{{$module_key}}" <?php if (in_array($module_key, $editmodule)) echo "checked='checked'"; ?> /> {{$module}}<br/>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info waves-effect waves-light m-r-10 role_update">Submit</button>
                    <button type="reset" class="btn btn-inverse waves-effect waves-light" onclick="javascript:history.go(-1)">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection