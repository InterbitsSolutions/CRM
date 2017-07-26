@extends('layouts.adminDashboard')

@section('page_title', 'Add Field')

@section('content')
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Add Field {{ $fieldType }}</h4>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <h3 class="box-title">Basic Information</h3>
            <form class="form-material form-horizontal m-t-30" action="{{ url()->current() }}" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <div class="form-group">
                <label class="col-md-12" for="example-text">Field Name</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="field_name" class="form-control" value="" placeholder="field name" required="">
                </div>
              </div>

              <div class="form-group">
                <label class="col-md-12" for="example-text">Field Value</label>
                <div class="col-md-12">
                  <input type="text" id="example-text" name="field_value" class="form-control" value="" placeholder="field value" required="">
                </div>
              </div>

              <?php if($fieldType=='region'){ ?>
                  <div class="form-group">
                    <label class="col-md-12" for="example-text">Field Code</label>
                    <div class="col-md-12">
                        <input type="text" id="example-text" name="field_code" class="form-control" value="" placeholder="field code" required="">
                    </div>
                  </div>
              <?php } ?>

              <button type="submit" class="btn btn-info waves-effect waves-light m-r-10">Save</button>
              <button type="button" onclick="javascript:history.go(-1)" class="btn btn-inverse waves-effect waves-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
 </div>
@endsection