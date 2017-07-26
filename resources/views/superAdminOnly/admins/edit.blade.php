@extends('layouts.adminDashboard')

@section('page_title', 'Edit '.$admin->name)

@section('content') 

	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">Manage Admin Information</h4>
			</div>
		</div>
		<!-- /row -->
		<div class="row">
			<div class="col-sm-12">
				<div class="white-box">
					<h3 class="box-title">Basic Information</h3>
						<form class="form-material form-horizontal m-t-30" action="/admins/{{ $admin->id }}" method="post">
						<div class="form-group">
							<label class="col-md-12" for="example-text">Full Name</label>
							<div class="col-md-12">
								<input type="text" id="example-text" name="example-text" class="form-control" value="{{ $admin->name }}" placeholder="Full Name">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-12" for="example-text">Email</label>
							<div class="col-md-12">
								<input type="text" id="example-text" name="example-text" class="form-control" value="{{ $admin->email }}" placeholder="Email">
							</div>
						</div>
						<button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Submit</button>
						<button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
					</form>
				</div>
			</div>
		</div>
		{{--<a href="{{ url('/admins') }}" class="btn btn-info"><span class="fa fa-angle-double-left"></span> Back to Admin</a>--}}

	{{--<form action="/admins/{{ $admin->id }}" method="post">--}}
		{{--<input type="hidden" name="_method" value="PUT">--}}
		{{--<div class="form-group">--}}
			{{--<label for="name" class="sr-only">Full Name</label>--}}
			{{--<input type="text" class="form-control" name="name" value="{{ $admin->name }}" id="name" placeholder="Full Name">--}}
		{{--</div>--}}
		{{--<div class="form-group">--}}
			{{--<label for="email" class="sr-only">Email</label>--}}
			{{--<input type="email" class="form-control" name="email" value="{{ $admin->email }}" id="email" placeholder="Email">--}}
		{{--</div>--}}
		{{--<div class="form-group">--}}
			{{--{{ csrf_field() }}--}}
			{{--<button class="btn btn-primary btn-block">Update Admin</button>--}}
		{{--</div>--}}
	{{--</form>--}}
</div>

@endsection