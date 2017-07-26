@extends('layouts.dataTable')

@section('page_title', 'Buckets')

@section('content')
  <!-- Page Content -->
  <div id="page-wrapper">
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Manage Buckets</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
          <ol class="breadcrumb">
            <li><a href="#">Dashboard</a></li>
            <li class="active">All Buckets</li>
          </ol>
        </div>
      </div>
      <!-- /row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="white-box">
            <div class="table-responsive">
              <table id="example23" class="table table-striped table_grid">
                <thead>
                <tr>
                  <th>Bucket Name</th>
                  <th>Bucket Region</th>
                  <th>Actions</th>
                </tr>
                </thead>
                <?php  echo '<pre>'; ?>
                <tbody>
                @foreach($contents['Buckets'] as $content)
                  <tr>
                    <td>{{ $content['Name'] }}</td>
                    <td>-</td>
                    <td class="record_actions">
                      <!--duplicate bucket button-->
                      <a data-toggle="modal" data-target="#duplicate_bucket_dialog" target="_blank" class="btn btn-primary duplicate_bucket"
                         onclick="$('#dbucket_name, #dbucket_region').val(''); $('#duplicateFor').val($(this).parent().parent().find('td:first-child').html());">Duplicate</a>
                      <!--delete bucket button-->
                      <a data-toggle="modal" class="btn btn-danger deleteBucket">Delete</a>
                    </td>
                  </tr>
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
  <div id="duplicate_bucket_dialog" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <form name="duplicate_bucket" id="duplicate_bucket" action="{{ url('/duplicate-bucket') }}" method="post">
        <input type="hidden" id="dpass_token" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" id="duplicateFor" name="duplicate_for" value="">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h2>Please provide duplicate bucket details</h2>
            <div class="row" style="padding: 0 0 10px 10px;">
              Bucket Name : <input type="text" name="dbucket_name" id="dbucket_name"><span></span>
            </div>
            <div class="row" style="padding: 0 0 10px 10px;">
              Bucket Region: <select name="dbucket_region" id="dbucket_region">
                <option value="">Please select Region</option>
                <option value="au">Australia (AU)</option>
                <option value="fr">France (FR)</option>
                <option value="jp">Japan (JP)</option>
              </select>
              <span></span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="duplicateBucket" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <!-- /.container-fluid -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
      $('#dbucket_name, #dbucket_region, #duplicateFor').val('');
      //duplicate bucket
      $(document).on('click','#duplicateBucket',function(){
        var bucketName = $('#dbucket_name').val();
        var bucketRegion = $('#dbucket_region').val();
        var duplicateFor = $('#duplicateFor').val();
        var passToken = $('#dpass_token').val();
        var url = '{{ url('/duplicate-dummybucket') }}';
        var successRedirect = '{{ url('/dummy-buckets') }}';
        customValid = true;
        var totalError  = [];
        var totalSucess = [];
        if(bucketName==""){
          totalError.push('dbucket_name');
        }else{
          totalSucess.push('dbucket_name');
        }
        if(bucketRegion==""){
          totalError.push('dbucket_region');
        }else{
          totalSucess.push('dbucket_region');
        }
        if(totalError.length>0)
        {
          for (count = 0; count <= totalError.length; count++) {
            $('#' + totalError[count]).css({"background-color": "#F2C1C1", "border": "1px solid #FF0000"});
            $('#' + totalError[count]).parent().find('span').html("This field can not be empty!");
            customValid = false
          }
        }
        if(totalSucess.length>0)
        {
          for (count = 0; count <= totalSucess.length; count++) {
            $('#' + totalSucess[count]).css({"background-color": "#FFFFFF", "border": "1px solid #D7D7D7"});
            $('#' + totalSucess[count]).parent().find('span').html('');
          }
        }
        if (customValid == false){
          return false;
        }else{
          $.ajax({
            type: 'POST',
            'url': url,
            async: false,
            data: {
              '_token': passToken,
              'bucket_name': bucketName,
              'bucket_region': bucketRegion,
              'duplicate_for': duplicateFor,
            },
            success:function(data){
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
          return false;
        }
      });

      //Delete bucket
      $(document).on('click','.deleteBucket',function(){
        var passToken = $('#dpass_token').val();
        var bucketName = $(this).parent().parent().find('td:first-child').html();
        var url = '{{ url('/delete-dummybucket') }}';
        var successRedirect = '{{ url('/dummy-buckets') }}';
        if(confirm("Are you sure to delete bucket?")){
          $.ajax({
            type: 'POST',
            'url': url,
            async: false,
            data: {
              '_token': passToken,
              'bucket_name': bucketName,
            },
            success:function(data){
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
  <!-- /#page-wrapper -->
@endsection
