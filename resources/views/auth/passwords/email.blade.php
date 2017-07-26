@extends('layouts.app')

@section('page_title', 'Reset Password')

@section('content')
    <form role="form" method="POST" action="{{ url('/password/email') }}">
        <h3 class="box-title m-b-20">Reset Password</h3>
        {{ csrf_field() }}
        <div class="form-group">
            <input id="email" type="email" class="form-control" name="email" placeholder="E-Mail Address" value="{{ old('email') }}">
            <a href="{{ url('/') }}" id="to-recover" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i> click here to login</a>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-info btn-block waves-effect waves-light">
                <i class="fa fa-btn fa-envelope"></i> Send Password Reset Link
            </button>
        </div>
    </form>
@endsection
