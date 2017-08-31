@extends('layouts.dataTable')
@section('page_title', 'Manage Multiple Buckets')
@section('content')
<!-- Page Content -->
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Manage Multiple Buckets <?php if(!empty(Input::get('type')) && !empty(Input::get('x'))){ echo 'for '.strtoupper(Input::get('x')); }; ?></h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">All Buckets</li>
            </ol>
        </div>
        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
            <div class="pull-right"><h2 class="bucket-total">{{ count($contents['Buckets']) }}<p> Buckets</p></h2></div>
        </div>
    </div>
    <form class="form-material form-horizontal m-t-30" id="awsNetworks" action="{{ url('/multiple-buckets') }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <ul class="nav navbar-top-links navbar-right padding-10">
            <span>AWS Server</span>
            <select style="margin: 8px 12px 0px 0px; width: auto;" name="aws_networks[]" multiple="multiple">
                @foreach($configAuth as $config)
                <?php $selected = (in_array($config->id, $selectedNetworks)) ? 'selected="selected"' : ''; ?>
                <option value="{{ $config->id }}" <?php echo $selected ?>>{{ $config->aws_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-info waves-effect waves-light m-r-10" onclick="$('#overlay').show();">Show Buckets</button>
        </ul>
    </form>

    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box clearfix">
                <div class="m-b-10 pull-left">
                    <a href="#"><span class="label label-info" id="bulkDelete">Bulk delete</span></a>
                    <a href="#"><span class="label label-info" id="copyToClipboard">Copy to Clipboard</span></a>
                </div>
                <div class="m-b-10 pull-right"><label>Bucket URL: <label>
                            <input type="radio" name="showLink" id="withParams" checked> With Ringba Params
                            <input type="radio" name="showLink" id="withoutParams"> Without Params
                </div>
                <div class="table-responsive col-sm-12">
                    <table id="example23" class="table table-striped table_grid">
                        <thead>
                        <tr>
                            <th><input type="checkbox" class="bulkCheckbox"></th>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($contents['Buckets'] as $content)
                        <?php
                        //check bucket location
                        $bucketConfigAuth = (!empty($content['bucketConfigAuth'])) ? $content['bucketConfigAuth'] : 1;
                        $locationConstraint = (!empty($content['LocationConstraint'])) ? $content['LocationConstraint'] : 'eu-central-1';
                        $urlConcatOperator = (!empty($locationConstraint) && $locationConstraint=='ap-northeast-1') ? '-' : '.';
                        $urls="http://".$content['Name'].".s3-website".$urlConcatOperator.$locationConstraint.".amazonaws.com";
                        //replace string, get unique number and get final string of the BUCKET
                        $firstString = substr($content['Name'], 0, strcspn($content['Name'], '1234567890'));
                        $replaceCommonString = str_replace(array($firstString,'.com'), '' , $content['Name']);

                        if (preg_match('#[0-9]#',$replaceCommonString)){
                            $getUniqueNumber = getNumericVal($replaceCommonString);
                            //replace first find string from string
                            $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
                        }else{
                            $finalString = $replaceCommonString;
                        }
                        //get NETWORK name
                        $networkName = '';
                        if (preg_match('#[0-9]#',$finalString)){
                            $getUniqueNumber = getNumericVal($finalString);
                            //replace first find string from string
                            $networkName =  preg_replace("/$getUniqueNumber/", '', $finalString, 1);
                        }else{
                            $networkName = $finalString;
                        }
                        //second string
                        $finalStringNew = substr($finalString, 0, strcspn($finalString, '1234567890'));
                        $regionNetwork = substr($finalStringNew, 0, 3).substr( $finalStringNew, -1 );

                        //add PID as MID from DB in params string
                        $paramsArray = array();
                        if(array_key_exists($finalString, $bucketPidArr)){
                            $paramsArray[] = 'mid='.$bucketPidArr[$finalString]['bucket_pid'];
                            //                                    $paramsArray[] = 'phid='.$bucketPidArr[$finalString]['bucket_phone_number'];
                        }
                        $basicParams = (!empty($paramsArray)) ? '/?'.implode('&',$paramsArray) : '';
                        //RINGA params
                        if(array_key_exists($finalString, $bucketPidArr)){
                            if(!empty($bucketPidArr[$finalString]['ringba_code'])) { $paramsArray[] = 'rb='.$bucketPidArr[$finalString]['ringba_code'];}
                        }
                        //embed params from DB in string
                        if(array_key_exists($regionNetwork, $bucketParamArr)){
                            $networkName = (!empty($networkName)) ? $networkName : $bucketParamArr[$regionNetwork]['bucket_short_code'];
                            $paramsArray[] = $bucketParamArr[$regionNetwork]['bucket_parameters'].'&network='.$networkName;
                        }
                        $concatParams = implode('&',$paramsArray);
                        $embedString = (!empty($concatParams)) ? '/?'.$concatParams : '';
                        ?>
                        <tr>
                            <td> <input type="checkbox" name="bucketNames[]" value="{{ $content['Name'].'_'.$bucketConfigAuth.'_'.$locationConstraint }}" class="bucket_checkbox"></td>
                            <td class="currentBucketName">{{ $content['Name'] }}</td>
                            <td class="currentBucketUrl">
                                <a class="active" href="{{ $urls.$embedString }}">{{ $urls.$embedString }}</a>
                                <a class="" href="{{ $urls.$basicParams }}">{{ $urls.$basicParams }}</a>
                                <a class="" href="{{ $urls }}">{{ $urls }}</a></td>
                            <td>{{ date('Y-m-d H:i:s', strtotime($content['CreationDate'])) }}</td>
                            <td class="record_actions">
                                <!--duplicate bucket button-->
                                <a data-toggle="modal" title="Duplicate Bucket" data-target="#duplicate_bucket_dialog" target="_blank" class="btn btn-primary1 duplicate_bucket"
                                   onclick="$('#dbucket_name').val('');
                                                    $('#duplicateFor').val($(this).parent().parent().find('td.currentBucketName').html());
                                                    $('#dbucket_name').val($(this).parent().parent().find('td.currentBucketName').html());
                                                    $('#bucketDuplicateFor').html($(this).parent().parent().find('td.currentBucketName').html());
													$('#searchItem').val($('div.dataTables_filter input').val());
                                                    $('#duplicateForRegion').val('{{$locationConstraint}}');
                                                    $('#duplicateAuthID').val('{{$bucketConfigAuth}}');
                                                    ">
                                    <i class="fa fa-clone"></i>
                                </a>
                                <!--delete bucket button-->
                                <a data-toggle="modal" title="Delete Bucket" class="btn btn-danger1 deleteBucket" data-authID="{{ $bucketConfigAuth }}" data-region="{{ $locationConstraint }}"><i class="fa fa-trash"></i></a>
                                <a data-toggle="modal" title="Copy Bucket URL" class="btn btn-danger1 copyBucketUrl"><i class="fa fa-copy"></i></a>
                                <a data-toggle="modal" title="Preview Bucket URL" class="btn btn-danger1 previewBucketUrl"><i class="fa fa-history"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
</div>
<!--SHOW DIALOG BOX on the basis of total buckets-->
<?php if(count($contents['Buckets'])>=100){ ?>
    <div id="duplicate_bucket_dialog_100" class="modal fade form-group" role="dialog">
        <input type="hidden" id="dpass_token" name="_token" value="{{ csrf_token() }}">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Bucket Alert</h4>
                </div>
                <div class="modal-body">
                    <p>You have exceed the limit of buckets, please delete some buckets and process further!!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php }else{ ?>
    <div id="duplicate_bucket_dialog" class="modal fade form-group" role="dialog">
        <div class="modal-dialog">
            <form name="duplicate_bucket" id="duplicate_bucket" action="{{ url('/duplicate-bucket') }}" method="post">
                <input type="hidden" id="dpass_token" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="duplicateFor" name="duplicate_for" value="">
                <input type="hidden" id="duplicateForRegion" name="duplicate_for_region" value="">
                <input type="hidden" id="duplicateAuthID" name="duplicate_auth_id" value="">
                <input type="hidden" id="duplicateRegion" name="duplicate_for" value="">
                <input type="hidden" id="searchItem" name="searchItem" value="">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Duplicate bucket details:</h2>
                        <div class="row" style="padding: 0 0 10px 10px;">
                            Are you sure you want to create a duplicate Bucket from : <p id="bucketDuplicateFor"></p>
                        </div>
                        Please select number of buckets:
                        <select name="create_count" id="duplicate_counter">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="duplicateBucket" class="btn btn-primary">Create</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
<!-- Modal -->
<a data-toggle="modal" data-target="#bucketAlert" target="_blank" class="btn btn-primary bucketAlert_anchor"></a>
<div class="modal fade" id="bucketAlert" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Bucket Alert</h4>
            </div>
            <div class="modal-body">
                <p>You are going to exceed the limit of buckets, please delete some buckets and process further!!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--- copy to aws popup -->
<div id="copytoaws_bucket_dialog" class="modal fade form-group " role="dialog">
    <div class="modal-dialog">
        <form name="copytoaws_bucket" id="copytoaws_bucket" action="{{ url('/duplicate-bucket-to-aws') }}" method="post">
            <input type="hidden" id="dpass_token_aws" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="duplicateForToAws" name="duplicate_for" value="">
            <input type="hidden" id="duplicateToAwsRegion" name="duplicate_aws_region" value="">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Select AWS Server:</h2>
                    <select name="create_count" class="form-control" id="aws_server_id" required>
                        @foreach($allAwsServer as $allAwsServerVal)
                        <option value="{{$allAwsServerVal['id']}}">{{ ucwords($allAwsServerVal['aws_name'])}}</option>
                        @endforeach
                    </select>
                    <h2>New Bucket Name:</h2>
                    <input type="text" name="new_bucket_name" id="new_bucket_name" required class="form-control">
                    <p style="color:red;">i.e. Just change the url or bucket name as per your requirement</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="duplicateBucketToAws" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- copy to aws popup -->
<!--- phonenumber popup -->
<div id="update_phone_dialogue" class="modal fade form-group " role="dialog">
    <div class="modal-dialog">
        <form name="phone_number_update_form" id="phone_number_update_form" action="{{ url('/duplicate-bucket-to-aws') }}" method="post">
            <input type="hidden" id="dpass_token_aws" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="template_name" name="template_name" value="">
            <input type="hidden" id="region" name="region" value="">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Update Phone Number:</h2>
                    <input type="text" name="phone_number" id="phone_number" required class="form-control">
                    <p style='color:red;'>Please add the number in proper format</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="phone_number_update" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- phone number popup -->
<!-- /.container-fluid -->
<?php $searchValue = (!empty($_GET['x'])) ? $_GET['x'] : '';?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
    $(window).load(function() {
        var searchItemValue = '<?php echo $searchValue; ?>';
        $('div.dataTables_filter input').val(searchItemValue).trigger('keyup');
    });
</script>
<script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
        $('#withParams').trigger('click');
        $('input:checkbox').removeAttr('checked');
        //show url on the basis of selected by User
        //show url on the basis of selected by User
        $('#withParams').click(function(){
            $('.currentBucketUrl a').removeClass('active');
            $('.currentBucketUrl a:first-child').addClass('active');
        });
        /* $('#withBasicParams').click(function(){
         $('.currentBucketUrl a').removeClass('active');
         $('.currentBucketUrl a:nth-child(2)').addClass('active');
         }); */
        $('#withoutParams').click(function(){
            $('.currentBucketUrl a').removeClass('active');
            $('.currentBucketUrl a:nth-child(3)').addClass('active');
        });

        //select all checkbox at single click
        $('.bulkCheckbox').click(function() {
            if ($(this).prop('checked')) {
                $('.bucket_checkbox').prop('checked', true);
            } else {
                $('.bucket_checkbox').prop('checked', false);
            }
        });
        //Copy Link
        $('.copyBucketUrl').click(function() {
            var bucketUrl = $(this).parent().parent().find('td.currentBucketUrl a.active').html();
            bucketUrl = replaceAll("&amp;", "&", bucketUrl);
            copyToClipboard(bucketUrl);
        });
        //Preview Link
        $('.previewBucketUrl').click(function() {
            var bucketUrl = $(this).parent().parent().find('td.currentBucketUrl a.active').html();
            window.open(bucketUrl, '_blank');
        });

        //check for duplicate event
        $('#dbucket_name, #duplicateFor').val('');
        var totalBuckets = '<?php echo count($contents['Buckets']) + count($masterBuckets)?>';
        if(totalBuckets>95) { jQuery('.bucketAlert_anchor').trigger('click');}
        //duplicate bucket
        $(document).on('click','#duplicateBucket',function(){
            var duplicateFor = $('#duplicateFor').val();
            var duplicateForRegion = $('#duplicateForRegion').val();
            var duplicateAuthID = $('#duplicateAuthID').val();
            var duplicateCounter = $('#duplicate_counter').val();
            var passToken = $('#dpass_token').val();
            var url = '{{ url('/multiple-duplicate-bucket') }}';
            var searchItem = $('div.dataTables_filter input').val();
            var successRedirect = (searchItem!='') ? '{{ url('/multiple-buckets') }}?x='+searchItem : '{{ url('/multiple-buckets') }}';
            customValid = true;
            var totalError  = [];
            var totalSuccess = [];
//                    if(bucketName==""){
//                        totalError.push('dbucket_name');
//                    }else{
//                        totalSuccess.push('dbucket_name');
//                    }
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
                $('div#overlay').show();
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'duplicate_counter': duplicateCounter,
                        'duplicate_for': duplicateFor,
                        'duplicate_auth_id': duplicateAuthID,
                        'duplicate_for_region': duplicateForRegion
                    },
                    success:function(data){
                        $('div#overlay').hide();
                        var res = jQuery.parseJSON(data);
                        if(res.type=='success'){
                            $('#awsNetworks').attr('action', successRedirect)
                            $('#awsNetworks').submit();
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
            var authConfigID = $(this).attr('data-authID');
            var bucketRegion = $(this).attr('data-region');
            var bucketName = $(this).parent().parent().find('td.currentBucketName').html();
            var url = '{{ url('/multiple-delete-bucket') }}';
            var searchItem = $('div.dataTables_filter input').val();
            var successRedirect = (searchItem!='') ? '{{ url('/multiple-buckets') }}?x='+searchItem : '{{ url('/multiple-buckets') }}';
            if(confirm("Are you sure to delete bucket?")){
                $('#overlay').show();
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'bucket_name': bucketName,
                        'auth_config_id': authConfigID,
                        'bucket_region': bucketRegion
                    },
                    success:function(data){
                        $('#overlay').hide();
                        var res = jQuery.parseJSON(data);
                        if(res.type=='success'){
//                                window.location.href = successRedirect;
                            $('#awsNetworks').attr('action', successRedirect)
                            $('#awsNetworks').submit();
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
            var url = '{{ url('/multiple-bulk-delete') }}';
            var passToken = $('#dpass_token').val();
            var searchItem = $('div.dataTables_filter input').val();
            var successRedirect = (searchItem!='') ? '{{ url('/multiple-buckets') }}?x='+searchItem : '{{ url('/multiple-buckets') }}';
            if(confirm("Are you sure to delete bucket?")){
                $('#overlay').show();
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'bucket_name': bucketNames
                    },
                    success:function(data){
                        $('#overlay').hide();
                        var res = jQuery.parseJSON(data);
                        if(res.type=='success'){
//                                window.location.href = successRedirect;
                            $('#awsNetworks').attr('action', successRedirect)
                            $('#awsNetworks').submit();
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
        //copy bucket links to clipboard
        $(document).on('click','#copyToClipboard',function(){
            var bucketNames = [];
            var atLeastOneIsChecked = $('input[name=\"bucketNames[]\"]:checked').length > 0;
            if (atLeastOneIsChecked) {
                $('input[name=\"bucketNames[]\"]:checked').each(function () {
                    var bucketURL = $(this).parent().parent().find('td.currentBucketUrl a.active').html();
                    bucketURL = replaceAll("&amp;", "&", bucketURL);
                    bucketNames.push(bucketURL);
                });
                var newArr = bucketNames.join(',').replace(/,/g, '\r\n').split();
            }else{
                alert('Please Select At Least One Bucket');
                return false;
            }
            copyToClipboard(newArr);
        });
        //copy to clipboard
        function copyToClipboard(selectedLink) {
            // alert(selectedLink);
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(selectedLink).select();
            document.execCommand("copy");
            $temp.remove();
        }
    });
    //duplciate bucket to aws
    $(document).on('click','#duplicateBucketToAws',function(){
        var duplicateFor                = $('#duplicateForToAws').val();
        var duplicateToAwsRegion        = $('#duplicateToAwsRegion').val();
        var awsServerId                 = $('#aws_server_id').val();
        var passToken                   = $('#dpass_token_aws').val();
        var new_bucket_name             = $('#new_bucket_name').val();
        var url = '{{ url('/duplicate-bucket-to-aws') }}';
        // alert(duplicateFor);
        var successRedirect = '{{ url('/buckets') }}';
        $('div#overlay').show();
        $.ajax({
            type: 'GET',
            'url': url,
            async: false,
            data: {
                '_token': passToken,
                'duplicate_for': duplicateFor,
                'duplicateToAwsRegion': duplicateToAwsRegion,
                'aws_server_id': awsServerId,
                'new_bucket_name': new_bucket_name,
            },
            success:function(data){
                $('div#overlay').hide();
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
    });
    //update phone
    $(document).on('click','#phone_number_update',function(){
        var bucket_name                 = $('#template_name').val();
        var region                      = $('#region').val();
        var phone_number                = $('#phone_number').val();
        var passToken                   = $('#dpass_token_aws').val();
        var searchItem                  = $('div.dataTables_filter input').val();
        var url                         = '{{ url('/update-phone-xml-fie') }}';
        // alert(duplicateFor);
        var successRedirect = '{{ url('/buckets') }}?x='+searchItem;
        $('div#overlay').show();
        $.ajax({
            type: 'GET',
            'url': url,
            async: false,
            data: {
                '_token': passToken,
                'bucket_name': bucket_name,
                'region': region,
                'phone_number': phone_number,
            },
            success:function(data){
                $('div#overlay').hide();
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
    });
    $(document).on('click', '.paginate_button', function(){
        if($('#withoutParams').is(':checked')) { $('#withoutParams').trigger('click'); }
        if($('#withParams').is(':checked')) { $('#withParams').trigger('click'); }
    });
    function replaceAll(find, replace, str)
    {
        while( str.indexOf(find) > -1)
        {
            str = str.replace(find, replace);
        }
        return str;
    }
</script>
<style>
    .modal{z-index: 8888;}
    .currentBucketUrl a{display:none;}
    .currentBucketUrl a.active{display:block;}
</style>
<?php
function getNumericVal ($str) {
    preg_match_all('/\d+/', $str, $matches);
    return (!empty($matches[0][0])) ? $matches[0][0] : '';
}
?>
@endsection