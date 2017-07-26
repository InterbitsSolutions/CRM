@extends('layouts.dataTable')

@section('page_title', 'Templates')

@section('content')
  <!-- Page Content -->
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Templates</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
          <ol class="breadcrumb">
            <li><a href="#">Dashboard</a></li>
            <li class="active">All Templates</li>
          </ol>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <a href="#"><span class="label label-info" id="bulkDelete">Bulk delete</span></a>
            <div class="table-responsive">
              <table id="example23" class="table table-striped table_grid">
                <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>URL</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($contents['Buckets'] as $content)
                  <?php
                    //show template master
                    $masterTemplates = array('www.support.microsoftaffr.com', 'www.support.microsoftadfr.com');
                    if (in_array($content['Name'], $masterTemplates)){
                      $location = $s3client->getBucketLocation(array(
                              'Bucket' => $content['Name']
                          ));
                      $urls="http://".$content['Name'].".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                  ?>
                  <tr>
                    <td> <input type="checkbox" name="bucketNames[]" value="{{ $content['Name'] }}"></td>
                    <td class="currentBucketName">{{ $content['Name'] }}</td>
                    <td><a href="{{ $urls }}">{{ $urls }}</a></td>
                    <td>{{ date('Y-m-d H:i:s', strtotime($content['CreationDate'])) }}</td>
                    <td class="record_actions">
                      <!--duplicate bucket button-->
                      <a data-toggle="modal" title="Duplicate" data-target="#duplicate_bucket_dialog" target="_blank" class="btn btn-primary1 duplicate_bucket"
                         onclick="$('#dbucket_name').val(''); $('#duplicateFor').val($(this).parent().parent().find('td.currentBucketName').html());$('#dbucket_name').val($(this).parent().parent().find('td.currentBucketName').html());"> <i class="fa fa-clone"></i></a>
                      <!--delete bucket button-->
                      <a data-toggle="modal" class="btn btn-danger1 deleteBucket"><i class="fa fa-trash"></i></a>
                    </td>
                  </tr>
                  <?php } ?>
                @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
  </div>
  <div id="duplicate_bucket_dialog" class="modal fade form-group" role="dialog">
    <div class="modal-dialog">
      <form name="duplicate_bucket" id="duplicate_bucket" action="{{ url('/duplicate-bucket') }}" method="post">
        <input type="hidden" id="dpass_token" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" id="duplicateFor" name="duplicate_for" value="">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h2>Please provide duplicate bucket details</h2>
            <div class="row" style="padding: 0 0 10px 10px;">
              Bucket Name : <input class="form-control" type="text" name="dbucket_name" id="dbucket_name"><span></span>
              <p style="color:red;">Please change Bucket name</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="duplicateBucket" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  <!-- /.container-fluid -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
      $('#dbucket_name,#duplicateFor').val('');
      //Delete bucket
      $(document).on('click','.deleteBucket',function(){
        var passToken = $('#dpass_token').val();
        var bucketName = $(this).parent().parent().find('td.currentBucketName').html();
        var url = '{{ url('/delete-bucket') }}';
        var successRedirect = '{{ url('/buckets') }}';
        if(confirm("Are you sure to delete bucket?")){
          $('#overlay').show();
          $.ajax({
            type: 'POST',
            'url': url,
            async: false,
            data: {
              '_token': passToken,
              'bucket_name': bucketName,
            },
            success:function(data){
              $('#overlay').hide();
              var res = jQuery.parseJSON(data);
              if(res.type=='success'){
                window.location.href = successRedirect;
                return false;
              }
              if(res.type=='error'){
                alert(res.message);
                return false;
              }
            }
          });
        }
      });
      //delete buckets in bulk
      $(document).on('click','#bulkDelete',function(){
        var bucketNames = [];
        var atLeastOneIsChecked = $('input[name=\"bucketNames[]\"]:checked').length > 0;
        if (atLeastOneIsChecked) {
          $('input[name=\"bucketNames[]\"]:checked').each(function () {
            bucketNames.push($(this).val());
          });
        }else{
          alert('Please Select At Least One Bucket');
          return false;
        }
        //confirm before delete buckets in bulk
        var url = '{{ url('/delete-multiple-bucket') }}';
        var passToken = $('#dpass_token').val();
        var successRedirect = '{{ url('/buckets') }}';
        if(confirm("Are you sure to delete bucket?")){
          $('#overlay').show();
          $.ajax({
            type: 'POST',
            'url': url,
            async: false,
            data: {
              '_token': passToken,
              'bucket_name': bucketNames,
            },
            success:function(data){
              $('#overlay').hide();
              var res = jQuery.parseJSON(data);
              if(res.type=='success'){
                window.location.href = successRedirect;
                return false;
              }
              if(res.type=='error'){
                alert(res.message);
                return false;
              }
            }
          });
        }
      });
    });
  </script>
  <style>
    .modal{z-index: 8888;}
   </style>
  <!-- /#page-wrapper -->
@endsection