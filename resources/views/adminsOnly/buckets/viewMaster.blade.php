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
                <div class="pull-right"><h2 class="bucket-total">{{ count($buckets) }}<p> Buckets</p></h2></div>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <div class="form-group">
                        <?php if($totalAwsBuckets>=100){ ?>
                            <a href="#" data-toggle="modal" data-target="#master_bucket_dialog" target="_blank"><button type="button" class="btn btn-info waves-effect waves-light m-r-10" id="addFiles">Add Master Bucket</button></a>
                        <?php }else{?>
                            <a href="{{ url('/add-master-bucket') }}">
                                <button type="button" class="btn btn-info waves-effect waves-light m-r-10" id="addFiles">Add Master Bucket</button>
                            <a>
                        <?php } ?>
                          {{--<a href="#" data-toggle="modal" data-target="#master_bucket_dialog" target="_blank">--}}
                            {{--<button type="button" class="btn btn-info waves-effect waves-light m-r-10" id="addFiles">Duplicate Bucket</button>--}}
                            {{--<a>--}}
                    </div>
                    <div class="table-responsive">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Bucket Name (template)</th>
                                <th>Bucket Region</th>
                                <th>Bucket Short Code</th>
                                <th>Bucket PID</th>
                                <th>Bucket Phone Number</th>
                                <th>Bucket Analytics ID</th>
                                <th>Ringba Code</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1;?>
                            @foreach($buckets as $bucket)
                                <tr>
                                <td>{{ $counter }}</td>
                                <td>{{ strtoupper($bucket->bucket_name) . '('.$bucket->template_name.')' }}</td>
                                <td>{{ (!empty($bucket->bucket_region)) ? $bucket->bucket_region : "-" }}</td>
                                <td>{{ (!empty($bucket->bucket_short_code)) ? $bucket->bucket_short_code : "-"  }}</td>
                                <td>{{ (!empty($bucket->bucket_pid)) ? $bucket->bucket_pid : "-"  }}</td>
                                <td>{{ (!empty($bucket->bucket_phone_number)) ? trim($bucket->bucket_phone_number) : "-"  }}</td>
                                <td>{{ (!empty($bucket->bucket_analytics_id)) ? $bucket->bucket_analytics_id : "-"  }}</td>
                                <td>{{ (!empty($bucket->ringba_code)) ? $bucket->ringba_code : "-"  }}</td>
                                <td class="record_actions">
                                    <a href="#" data-toggle="modal" data-target="#copy_master_bucket" target="_blank" onclick="$('#copyAwsServer').val('');$('#fromMasterBucket').val('{{$bucket->id}}')"  class="btn btn-primary-btn" title="Copy Master Bucket"><i class="fa fa-copy"></i></a>
                                    <a href="{{ url('/edit-master-bucket/'.$bucket->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                    <a href="{{ url('/delete-master-bucket/'.$bucket->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $bucket->bucket_name }} ?')"><i class="fa fa-trash"></i></a>
                                </td>
                                </tr>
                            <?php $counter++;?>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
<div id="copy_master_bucket" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form name="child_bucket_form" id="child_bucket_form" action="{{ url('/copy-master-bucket') }}" method="post">
            <!-- Modal content-->
            <div class="modal-content form-group">
                <div class="modal-header">
                    <h2>Copy Master Bucket</h2>
                    <input type="hidden" id="pass_token" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="fromMasterBucket" name="master_bucket_from" value="">
                    <div class="row" style="padding: 0 0 10px 10px;">
                        AWS Server:
                        <select name="copy_to_aws_server" id="copyAwsServer" class="form-control">
                            <option value="">Please select AWS server</option>
                            @foreach($configAuth as $config)
                                @if($config->status!='active')
                                    <option value="{{ $config->id }}">{{ $config->aws_name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="copyMasterBucket" class="btn btn-primary">Copy</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div><!-- /.container-fluid -->
<script>
    //add bucket from MASTER
    $(document).ready(function($){
        $(document).on('click','#copyMasterBucket',function(){
            var fromMasterBucket = $('#fromMasterBucket').val();
            var passToken = $('#pass_token').val();
            var copyAwsServer = $('#copyAwsServer').val();
            var copyAwsServerName = $('#copyAwsServer option:selected').text();
            var url = '{{ url('/copy-master-bucket') }}';
            var successRedirect = '{{ url()->current() }}';
            customValid = true;

            var totalError  = [];
            var totalSuccess = [];
            if(fromMasterBucket==""){
                totalError.push('master_bucket_from');
            }else{
                totalSuccess.push('master_bucket_from');
            }
            if(totalError.length>0)
            {
                for (count = 0; count <= totalError.length; count++) {
                    $('#' + totalError[count]).css({"background-color": "#F2C1C1", "border": "1px solid #FF0000"});
                    $('#' + totalError[count]).parent().find('span').html("This field can not be empty!");
                    customValid = false
                }
            }
            if(totalSuccess.length>0)
            {
                for (count = 0; count <= totalSuccess.length; count++) {
                    $('#' + totalSuccess[count]).css({"background-color": "#FFFFFF", "border": "1px solid #D7D7D7"});
                    $('#' + totalSuccess[count]).parent().find('span').html('');
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
                        'master_bucket_id': fromMasterBucket,
                        'aws_server_id': copyAwsServer,
                        'aws_server_name': copyAwsServerName
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