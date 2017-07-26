@extends('layouts.dataTable')

@section('page_title', 'Master Buckets')

@section('content')
<!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Master Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Master Buckets</li>
                </ol>
            </div>
            <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                <div class="pull-right"><h2 class="bucket-total">{{ count(contents['Buckets']) }}<p> Buckets</p></h2></div>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <div class="form-group">
                        <a href="#" data-toggle="modal" data-target="#master_bucket_dialog" target="_blank">
                            <button type="button" class="btn btn-info waves-effect waves-light m-r-10" id="addFiles">Duplicate Bucket</button>
                        <a>
                    </div>
                    <div class="table-responsive">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Bucket Name</th>
                                <th>Bucket Region</th>
                                <th>Bucket Short Code</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            {{--<tbody>--}}
                            {{--<?php $counter = 1;?>--}}
                            {{--@foreach($buckets as $bucket)--}}
                                {{--<tr>--}}
                                {{--<td>{{ $counter }}</td>--}}
                                {{--<td>{{ $bucket->bucket_name }}</td>--}}
                                {{--<td>{{ (!empty($bucket->bucket_region)) ? $bucket->bucket_region : "-" }}</td>--}}
                                {{--<td>{{ (!empty($bucket->bucket_short_code)) ? $bucket->bucket_short_code : "-"  }}</td>--}}
                                {{--<td class="record_actions">--}}
                                    {{--<a href="{{ url('/upload-master-files/'.$bucket->id) }}" title="edit" class="btn btn-primary1"><i class="fa fa-edit"></i></a>--}}
                                    {{--<a href="{{ url('/delete-master-bucket/'.$bucket->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $bucket->bucket_name }} ?')"><i class="fa fa-trash"></i></a>--}}
{{--                                    <a href="{{ url('/create-master-bucket/'.$bucket->id) }}" title="delete" class="btn btn-inverse" onclick="return confirm('Are you sure you want to create bucket from  {{ $bucket->bucket_name }}?')">Create Bucket</a>--}}
                                {{--</td>--}}
                                {{--</tr>--}}
                            {{--<?php $counter++;?>--}}
                            {{--@endforeach--}}
                            {{--</tbody>--}}

                            <tbody>
                            @foreach($contents['Buckets'] as $content)
                                <?php
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
                                        <a data-toggle="modal" data-target="#duplicate_bucket_dialog" target="_blank" class="btn btn-primary1 duplicate_bucket"
                                           onclick="$('#dbucket_name').val(''); $('#duplicateFor').val($(this).parent().parent().find('td.currentBucketName').html());$('#dbucket_name').val($(this).parent().parent().find('td.currentBucketName').html());"> <i class="fa fa-clone"></i></a>
                                        <!--delete bucket button-->
                                        <a data-toggle="modal" class="btn btn-danger1 deleteBucket"><i class="fa fa-trash"></i></a>
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
<!--add bucket option under left sidebar menu-->
<div id="master_bucket_dialog" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form name="child_bucket_form" id="child_bucket_form" action="{{ url('/add-bucket') }}" method="post">
            <!-- Modal content-->
            <div class="modal-content form-group">
                <div class="modal-header">
                    <h2>Duplicate Master Bucket</h2>
                    <input type="hidden" id="pass_token" name="_token" value="{{ csrf_token() }}">
                    <div class="row" style="padding: 0 0 10px 10px;">
                        Master Bucket:
                        <select name="master_child_bucket" id="master_child_bucket" class="form-control">
                            <option value="">Please select bucket</option>
                            @foreach($buckets as $bucket)
                                <option value="{{ $bucket->id }}">{{ $bucket->bucket_name }}</option>
                            @endforeach
                        </select>
                        <span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addChildBucket" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div><!-- /.container-fluid -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
        $(document).on('click','#addChildBucket',function(){
            var masterBucket = $('#master_child_bucket').val();
            var passToken = $('#pass_token').val();
            var url = '{{ url('/create-child-bucket') }}';
            var successRedirect = '{{ url('/list-child-buckets') }}';
            customValid = true;

            var totalError  = [];
            var totalSucess = [];
            if(masterBucket==""){
                totalError.push('master_child_bucket');
            }else{
                totalSucess.push('master_child_bucket');
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
                $('#overlay').show();
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'master_bucket': masterBucket,
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
                return false;
            }
        });
    });
</script>
@endsection