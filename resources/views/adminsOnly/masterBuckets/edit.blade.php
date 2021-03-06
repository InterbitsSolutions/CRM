@extends('layouts.adminDashboard')

@section('page_title', 'Edit Master Bucket')

@section('content')
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Edit Master Bucket</h4>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <h3 class="box-title">Basic Information</h3>
            <form class="form-material form-horizontal m-t-30" action="{{ url('/edit-master-bucket').DIRECTORY_SEPARATOR.$currentBucketDetails['id'] }}" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <!---BUCKET REGIONS-->
              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Region</label>
                <div class="col-md-12">
                    <select name="bucket_region" id="bucket_region" class="form-control" required="">
                      <option value="">Please select Region</option>
                      @foreach($bucketRegions as $regions)
                        <?php $selected = ($regions->region_value==$currentBucketDetails['bucket_region']) ? 'selected="selected"' : ''; ?>
                        <option value="{{ $regions->region_value }}" {{ $selected }}>{{ $regions->region_name }}</option>
                      @endforeach
                    </select>
                    <span></span>
                </div>
              </div>
              <!---BUCKET SHORT CODES-->
              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Short Code</label>
                <div class="col-md-12">
                  <select name="bucket_short_code" id="bucket_short_code" class="form-control" required="">
                    <option value="">Please select short code</option>
                    @foreach($bucketShortCodes as $shortCode)
                      <?php $selected = ($shortCode->shortcode_value==$currentBucketDetails['bucket_short_code']) ? 'selected="selected"' : ''; ?>
                      <option value="{{ $shortCode->shortcode_value }}" {{ $selected }}>{{ $shortCode->shortcode_name }}</option>
                    @endforeach
                  </select>
                  <span></span>
                </div>
              </div>
              <!---BUCKET BROWSERS-->
              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Browser</label>
                <div class="col-md-12">
                  <select name="bucket_browser" id="bucket_browser" class="form-control" required="">
                    <option value="">Please select browser</option>
                    @foreach($bucketBrowsers as $browser)
                      <?php $selected = ($browser->browser_value==$currentBucketDetails['bucket_browser']) ? 'selected="selected"' : ''; ?>
                      <option value="{{ $browser->browser_value }}" {{ $selected }}>{{ $browser->browser_name }}</option>
                    @endforeach
                  </select>
                  <span></span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-12" for="example-text">Phone Number</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="bucket_phone_number" class="form-control" value="{{$currentBucketDetails['bucket_phone_number']}}" placeholder="Phone Number" required="">
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12" for="example-text">PID</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="bucket_pid" class="form-control" value="{{$currentBucketDetails['bucket_pid']}}" placeholder="PID" required="">
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12" for="example-text">Analytics ID</label>
                <div class="col-md-12">
                    <input type="text" id="example-text" name="bucket_analytics_id" class="form-control" value="{{$currentBucketDetails['bucket_analytics_id']}}" placeholder="Analytics ID" required="">
                </div>
              </div>
              <div class="form-group">
                 <label class="col-md-12" for="example-text">Ringba Code</label>
                 <div class="col-md-12">
                      <input type="text" id="example-text" name="ringba_code" class="form-control" value="{{$currentBucketDetails['ringba_code']}}" placeholder="Ringba Code">
                  </div>
              </div>
              <!---BUCKET TEMPLATES-->
              <div class="form-group">
                <label class="col-md-12" for="example-text">Bucket Template</label>
                <div class="col-md-12">
                  <select name="bucket_template" id="bucket_template" class="form-control" required="">
                    <option value="">Please select short code</option>
                    @foreach($bucketTemplates as $template)
                      <?php $selected = ($template->id==$currentBucketDetails['bucket_template']) ? 'selected="selected"' : ''; ?>
                      <option value="{{ $template->id }}" {{ $selected }}>{{ $template->template_name }}</option>
                    @endforeach
                  </select>
                  <span></span>
                </div>
              </div>
              <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Save</button>
              <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
 </div>
@endsection