@extends('layouts.adminDashboard')

@section('page_title', 'Manage Configurations')

@section('content')

<?php //print_r($editmodule); exit; ?>
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Add Configuration</h4>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <h3 class="box-title">Basic Information</h3>
            <form class="form-material form-horizontal m-t-30" action="{{ url('/edit-user-role').DIRECTORY_SEPARATOR.$userRoles['id'] }}" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
			   <div class="form-group">
                <label class="col-md-12" for="example-text">Role Name</label>
                <div class="col-md-12">
                  <input type="text" id="role_name" name="role_name" class="form-control" value="{{ $userRoles->role_name }}" placeholder="Role Name" required="">
                </div>
              </div>
			  
			  <div class="form-group">
                  <label class="col-md-12" for="example-text">Permissions</label>
                  <div class="col-md-12">
                        @foreach($module_arr as $module_key => $module)
                           <input type="checkbox" name="modules[]" value="{{$module_key}}" <?php if(in_array($module_key,$editmodule)) echo "checked='checked'" ;?> /> {{$module}}<br/>
                        @endforeach
                  </div>
              </div>
              
              <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Submit</button>
              <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
@endsection