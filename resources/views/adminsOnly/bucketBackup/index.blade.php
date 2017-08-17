@extends('layouts.dataTable')

@section('page_title', 'Export Buckets')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Export Buckets</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Configurations</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box clearfix">
                    <div class="m-b-10 pull-left">
                        <a href="#"><span class="label label-info" id="createBucketBackup">Create backup</span></a>
                    </div>
                    <div class="table-responsive col-sm-12">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="bulkCheckbox"></th>
                                <th>No</th>
                                <th>Aws Server Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1; ?>
                            @foreach($awsBucketsArr as $awsDetails)
                                <tr>
                                    <td> <input type="checkbox" name="awsAccounts[]" value="{{ $awsDetails['id'] }}" class="bucket_checkbox"></td>
                                    <td>{{ $counter}}</td>
                                    <td>{{ $awsDetails['aws_name'] }}</td>
                                </tr>
                                <?php $counter++; ?>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <form method="POST" id="backup_form_submit" action="{{ url('/create-backup') }}">
         <input type="hidden" id="aws"name="aws_accounts[]" id="aws"/>
        <input type="hidden" id="dpass_token" name="_token" value="{{ csrf_token() }}">
    </form>

<script>
    $(document).ready(function () {
        $(".bucket_checkbox,.bulkCheckbox").attr('checked', false);
        //select all checkbox at single click
        $('.bulkCheckbox').click(function() {
            if ($(this).prop('checked')) {
                $('.bucket_checkbox').prop('checked', true);
            } else {
                $('.bucket_checkbox').prop('checked', false);
            }
        });
    });
     //create buckets backup
    $(document).on('click','#createBucketBackup',function() {
        var awsAccounts = [];
        var atLeastOneIsChecked = $('input[name=\"awsAccounts[]\"]:checked').length;
        if (atLeastOneIsChecked==1) {
            $('input[name=\"awsAccounts[]\"]:checked').each(function () {
               $('#aws').val($(this).val());
               $('#overlay').show();
               $('#backup_form_submit').submit();
            });
        } else {
            alert('Please Select only one AWS network!');
            return false;
        }
    });


    //create buckets backup
    $(document).on('click','#createBucketBackup1',function(){
        var awsAccounts = [];
        var atLeastOneIsChecked = $('input[name=\"awsAccounts[]\"]:checked').length > 0;
        if (atLeastOneIsChecked) {
            $('input[name=\"awsAccounts[]\"]:checked').each(function () {
                awsAccounts.push($(this).val());
            });
        }else{
            alert('Please Select at least one AWS network');
            return false;
        }
        //confirm before delete buckets in bulk
        var url = '{{ url('/create-backup') }}';
        var passToken = $('#dpass_token').val();
        var successRedirect = '{{ url('/bucketBackup.zip') }}';
        if(confirm("Are you sure to create bucket backup?")){
            $('#overlay').show();
            $.ajax({
                type: 'POST',
                'url': url,
                async: false,
                data: {
                    '_token': passToken,
                    'aws_accounts': awsAccounts
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
</script>
@endsection