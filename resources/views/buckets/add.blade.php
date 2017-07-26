@extends('layouts.adminDashboard')

@section('page_title', 'Add Master Bucket')

@section('content')
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Add Master Bucket</h4>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <h3 class="box-title">Basic Information</h3>
            <form class="form-material form-horizontal m-t-30" action="{{ url('/add-master-bucket') }}" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">

              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Name</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="bucket_name" class="form-control" value="" placeholder="Bucket Name" required="">
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Region</label>
                <div class="col-md-12">
                    <select name="bucket_region" id="bucket_region" class="form-control" required="">
                      <option value="">Please select Region</option>
                      <option value="au">Australia (AU)</option>
                      <option value="fr">France (FR)</option>
                      <option value="jp">Japan (JP)</option>
                    </select>
                    <span></span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12" for="example-text">Short Code</label>
                <div class="col-md-12">
                  <select name="bucket_short_code" id="bucket_short_code" class="form-control" required="">
                    <option value="">Please select short code</option>
                    <option value="ad">AD</option>
                    <option value="af">AF</option>
                    <option value="bc">BC</option>
                    <option value="ys">YS</option>
                  </select>
                  <span></span>
                </div>
              </div>

              <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Next</button>
              <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
 </div>
@endsection