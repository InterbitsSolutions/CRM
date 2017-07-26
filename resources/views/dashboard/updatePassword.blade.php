@extends('layouts.adminDashboard')

@section('page_title', 'Update Password')

@section('content')
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Update Password</h4>
                </div>
            </div>
            <!-- /row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="white-box">
                        <h3 class="box-title">Basic Information</h3>
                        <p>Enter your current password and then your new password to update your password</p>
                        <form class="form-material form-horizontal m-t-30" action="{{ url('/update-password') }}" method="post">
                            <div class="form-group">
                                <label class="col-md-12" for="example-text">Current Password</label>
                                <div class="col-md-12">
                                    <input type="password" name="currentPassword" id="currentPassword" placeholder="Current Password" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" for="example-text">New Password</label>
                                <div class="col-md-12">
                                    <input type="password" name="password" id="password" placeholder="New Password" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-12" for="example-text">Confirm Password</label>
                                <div class="col-md-12">
                                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" class="form-control" required="">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Submit</button>
                            <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            <p>Enter your current password and then your new password to update your password</p>
        </div>

    {{--<form action="/update-password" method="post">--}}
		{{--<div class="form-group">--}}
			{{--<label for="currentPassword" class="sr-only">Current Password</label>--}}
			{{--<input type="password" name="currentPassword" id="currentPassword" placeholder="Current Password" class="form-control">--}}
		{{--</div>--}}
        {{--<div class="form-group">--}}
            {{--<label for="password" class="sr-only">New Password</label>--}}
            {{--<input type="password" name="password" id="password" placeholder="New Password" class="form-control">--}}
        {{--</div>--}}
        {{--<div class="form-group">--}}
            {{--<label for="confirmPassword" class="sr-only">Confirm Password</label>--}}
            {{--<input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" class="form-control">--}}
        {{--</div>--}}
		{{--<div class="form-group">--}}
			{{--<input type="hidden" name="_method" value="put">--}}
            {{--{{csrf_field() }}--}}
            {{--<button class="btn btn-primary btn-block">Update Password</button>--}}
		{{--</div>--}}
    {{--</form>--}}
@endsection
