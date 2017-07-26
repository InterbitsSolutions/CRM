@extends('layouts.adminDashboard')

@section('page_title', 'Add Admin')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Add Admin</h4>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title">Basic Information</h3>
                    <form class="form-material form-horizontal m-t-30" action="{{ url('/admins') }}" method="post">
                        <div class="form-group">
                            <label class="col-md-12" for="example-text">Full Name</label>
                            <div class="col-md-12">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Full Name" value="{{ old('name') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12" for="example-text">Email</label>
                            <div class="col-md-12">
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}">
                            </div>
                        </div>
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
        {{--<a href="{{ url('/admins') }}" class="btn btn-info"><span class="fa fa-angle-double-left"></span> Back to Admin</a>--}}
    </div>

    {{--<form action="/admins" method="post">--}}
        {{--<div class="form-group">--}}
            {{--<label for="name" class="sr-only">Full Name</label>--}}
            {{--<input type="text" class="form-control" name="name" id="name" placeholder="Full Name" value="{{ old('name') }}">--}}
        {{--</div>--}}
        {{--<div class="form-group">--}}
            {{--<label for="email" class="sr-only">Email</label>--}}
            {{--<input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}">--}}
        {{--</div>--}}
        {{--<div class="form-group">--}}
            {{--{{ csrf_field() }}--}}
            {{--<button class="btn btn-primary btn-block">Add Admin</button>--}}
        {{--</div>--}}
    {{--</form>--}}
@endsection
