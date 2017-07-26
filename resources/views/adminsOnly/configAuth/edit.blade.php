@extends('layouts.adminDashboard')

@section('page_title', 'Manage Configurations')

@section('content')
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
            <form class="form-material form-horizontal m-t-30" action="{{ url('') }}/config/{{ $config->id }}" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
			   <div class="form-group">
                <label class="col-md-12" for="example-text">AWS Server Name</label>
                <div class="col-md-12">
                  <input type="text" id="aws_server_name" name="aws_server_name" class="form-control" value="{{ $config->aws_name }}" placeholder="AWS Server Name" required="">
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-12" for="example-text">Key</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="key" class="form-control" value="{{ $config->key }}" placeholder="Key" required="">
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-12" for="example-text">Secret</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="secret" class="form-control" value="{{ $config->secret }}" placeholder="Secret" required="">
                </div>
              </div>
              <div class="form-group">
                  <label class="col-md-12" for="example-text">Initial Counter</label>
                  <div class="col-md-12">
                      <input type="text" id="example-text" name="aws_counter" class="form-control" value="{{ $config->aws_counter }}" placeholder="Secret" required="">
                  </div>
              </div>
              {{--<div class="form-group">--}}
                  {{--<label class="col-md-12" for="example-text">Is Primary Network?</label>--}}
                  {{--<div class="col-md-12">--}}
                      {{--<p>--}}
                          {{--Yes <input type="radio" name="primary_network" value="yes" {{ ($config->primary_network=='yes') ? 'checked' : ''}}>--}}
                          {{--No <input type="radio" name="primary_network" value="no" {{ ($config->primary_network=='no') ? 'checked' : ''}}>--}}
                      {{--</p>--}}
                  {{--</div>--}}
              {{--</div>--}}
              {{--<div class="form-group">--}}
                {{--<label class="col-md-12" for="example-text">Status</label>--}}
                {{--<div class="col-md-12">--}}
                  {{--<p>--}}
                     {{--Active <input type="radio" name="status" value="active" {{$activeCheck}}>--}}
                     {{--In-Active <input type="radio" name="status" value="inactive" {{$inActiveCheck}} >--}}
                  {{--</p>--}}
                {{--</div>--}}
              {{--</div>--}}
              <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Submit</button>
              <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
@endsection