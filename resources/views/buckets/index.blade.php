@extends('layouts.adminDashboard')

@section('page_title', 'Admins')

@section('content')
<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
        </div>
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12 text-center">
                <a data-toggle="modal" data-target="#bucket_dialog" target="_blank" onclick="$('#bucket_name').val('')" class="btn btn-danger m-l-20 btn-rounded btn-outline hidden-xs hidden-sm waves-effect waves-light">Create Bucket</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        //add bucket name as input and add into DB
        $(document).ready(function($){
            $(document).on('click','#addBucket',function(){
                var bucketName = $('#bucket_name').val();
                var passToken = $('#pass_token').val();
                var url = '{{ url('/add-bucket') }}';
                var successRedirect = '{{ url('/list-buckets') }}';
                if(bucketName==''){
                    alert('Please enter bucket name!');
                    return false;
                }else{
                    $('#overlay').show();
                    $.ajax({
                        type: 'POST',
                        'url': url,
                        async: false,
                        data: {
                                '_token': passToken,
                                'bucket_name': bucketName
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
</div>
<div id="bucket_dialog" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form name="bucket_form" id="bucket_form" action="{{ url('/add-bucket') }}" method="post">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Please provide bucket details</h2>
                    <input type="hidden" id="pass_token" name="_token" value="{{ csrf_token() }}">
                    Bucket Name: <input type="text" name="bucket_name" id="bucket_name">
                </div>
                <div class="modal-footer">
                    <button type="button" id="addBucket" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- /#page-wrapper -->
@endsection