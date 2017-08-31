@extends('layouts.adminDashboard')

@section('page_title', 'Set Parameters')

@section('content')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Set Parameters</h4>
        </div>
    </div>
    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title">Basic Information {{$bucketParams->bucket_region}}</h3>
                <form class="form-material form-horizontal m-t-30" action="{{ url('/edit-bucket-params').DIRECTORY_SEPARATOR.$bucketParams['id'] }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <!---REGIONS-->
                    <div class="form-group">
                        <label class="col-md-12" for="example-text">Country Code</label>
                        <div class="col-md-12">
                            <select name="bucket_region" id="bucket_region" class="form-control" required="">
                                <option value="">Please select Network</option>
                                @foreach($bucketRegions as $regions)
                                <?php $selected = ($regions->region_value == $bucketParams->bucket_region) ? 'selected="selected"' : ''; ?>
                                <option value="{{ $regions->region_value }}" {{ $selected }}>{{ $regions->region_name }}</option>
                                @endforeach
                            </select>
                            <span></span>
                        </div>
                    </div>
                    <!---SHORT CODES-->
                    <div class="form-group">
                        <label class="col-md-12" for="example-text">Network</label>
                        <div class="col-md-12">
                            <select name="bucket_short_code" id="bucket_short_code" class="form-control" required="">
                                <option value="">Please select short code</option>
                                @foreach($bucketShortCodes as $shortCode)
                                <?php $selected = ($shortCode->shortcode_value == $bucketParams->bucket_short_code) ? 'selected="selected"' : ''; ?>
                                <option value="{{ $shortCode->shortcode_value }}" {{ $selected }}>{{ $shortCode->shortcode_name }}</option>
                                @endforeach
                            </select>
                            <span></span>
                        </div>
                    </div>
                    <!--DESCRIPTION-->
                    <div class="form-group">
                        <label class="col-md-12" for="example-text">Parameter String</label>
                        <div class="col-md-12">
                            <textarea class="form-control" rows="3" name="bucket_parameters">{{ $bucketParams->bucket_parameters }}</textarea>
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