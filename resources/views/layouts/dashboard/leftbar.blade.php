<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse slimscrollsidebar">
        <div class="user-profile">
            <div class="dropdown user-pro-body">
                <div><img src="{{ URL::asset('/') }}assests/plugins/images/users/admin.png" alt="user-img" class="img-circle"></div>
                <a href="#" class="dropdown-toggle u-dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo ucwords($login_user_display_name)?> <span class="caret"></span></a>
                <ul class="dropdown-menu animated flipInY">
                    <?php //if(Auth::user()->role == 1) {?>
                    <li><a href="{{ url('/update-password') }}"><i class="ti-settings"></i> Update Password</a></li> 
                    <?php //} ?> 
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ url('/logout') }}" data-method="post" data-token="{{ csrf_token() }}"><i class="fa fa-power-off"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <ul class="nav" id="side-menu">

            <li class="sidebar-search hidden-sm hidden-md hidden-lg">
                <!-- input-group -->
                <div class="input-group custom-search-form">
                    <input type="text" class="form-control" placeholder="Search...">
                    <span class="input-group-btn">
            <button class="btn btn-default" type="button"> <i class="fa fa-search"></i> </button>
            </span> </div>
                <!-- /input-group -->
            </li>
            <li class="nav-small-cap m-t-10 margin-left-10">&nbsp;&nbsp;&nbsp;&nbsp;Main Menu</li>
            <li> <a href="{{ url('/') }}" class="waves-effect"><i class="linea-icon linea-basic fa-fw" data-icon="v"></i> <span class="hide-menu"> Dashboard </span></a></li>
        <!--bucket menu-->

        @foreach($assigned_modules as $modules)
            @if($modules->module_name == 'Buckets')
            <li>
                <a href="#" class="waves-effect">
                    <i class="icon-handbag fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> Buckets </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/buckets') }}">Buckets</a> </li>
                    <li> <a href="#" data-toggle="modal" data-target="#master_bucket_dialog" target="_blank" onclick="$('#bucket_name').val('')">Add Bucket</a> </li>
                    <li> <a href="{{ url('/multiple-buckets') }}">Multiple Buckets</a> </li>
                </ul>
            </li>
            @endif           
            
            @if($modules->module_name == 'Master Bucket')
            <li>
                <a href="#" class="waves-effect">
                    <i class="fa fa-retweet" data-icon="v"></i>
                    <span class="hide-menu"> Master Bucket </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/list-master-buckets') }}">Master Bucket</a> </li>
                    <li> <a href="{{ url('/manage-bucket-fields') }}">Manage Bucket Fields</a> </li>
                </ul>
            </li>
            @endif

            @if($modules->module_name == 'Bucket Backup')
            <li>
                <a href="#" class="waves-effect">
                    <i class="fa fa-retweet" data-icon="v"></i>
                    <span class="hide-menu"> Bucket Backup </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/export-buckets') }}">Export Backup</a> </li>
                    <li> <a href="{{ url('/import-buckets') }}">Import Buckets</a> </li>
                </ul>
            </li>
            @endif

            
            @if($modules->module_name == 'Configurations')
            <li>
                <a href="#" class="waves-effect">
                    <i class="linea-icon linea-basic fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> Configurations </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/add-auth') }}">Add Configuration</a> </li>
                    <li> <a href="{{ url('/list-config') }}" >View Configurations</a> </li>
                </ul>
            </li>
            @endif

            @if($modules->module_name == 'Templates')
            <li>
                <a href="#" class="waves-effect">
                    <i class="icon-docs fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> Templates </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
					<li> <a href="{{ url('/add-template') }}">Add Templates</a> </li>
					<li> <a href="{{ url('/list-crm-templates') }}" >View Templates</a> </li>
					<li> <a href="{{ url('/export-template') }}" >Export Templates</a> </li>
                </ul>
            </li>
            @endif

            @if($modules->module_name == 'Parameters')
            <li>
                <a href="#" class="waves-effect">
                    <i class="linea-icon linea-software fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> Parameters </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/add-bucket-params') }}">Add Parameters</a> </li>
                    <li> <a href="{{ url('/list-bucket-params') }}" >View Parameters</a> </li>
                </ul>
            </li>
            @endif

            
            @if($modules->module_name == 'Backup')
            <li>
                <a href="{{ url('/script-backup') }}" class="waves-effect">
                    <i class="linea-icon linea-basic fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> Backup </span>
                </a>
            </li>
            @endif

            @if($modules->module_name == 'User')
             <li>
                <a href="#" class="waves-effect">
                    <i class="icon-docs fa-fw" data-icon="v"></i>
                    <span class="hide-menu"> User</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li> <a href="{{ url('/add-users') }}">Add User</a> </li>
                    <li> <a href="{{ url('/list-users') }}">View User</a> </li>
                    <li> <a href="{{ url('/add-user-role') }}">Add Role</a> </li>
                    <li> <a href="{{ url('/list-user-roles') }}" >View Roles</a> </li>
					     <li> <a href="{{ url('/user-log') }}" >User Log</a> </li>
                </ul>
            </li>
            @endif
        @endforeach
            
            <!--backup menu-->
            
            <!--configuration menu-->
            
            <!--template menu-->
            
            <!--parameter menu-->
            
            <!--Backup menu-->
            
            <!--User Roles menu-->
           
            
            <!--common menu-->

        <!--            {{--<li>--}}-->
        <!--                {{--<a href="javascript:void(0);" class="waves-effect"><i class="linea-icon linea-basic fa-fw text-danger" data-icon="7"></i>--}}-->
        <!--                    {{--<span class="hide-menu text-danger"> Multipurpose--}}-->
        <!--                        {{--<span class="fa arrow"></span>--}}-->
        <!--                    {{--</span>--}}-->
        <!--                {{--</a>--}}-->
        <!--                {{--<ul class="nav nav-second-level">--}}-->
        <!--                    {{--<li> <a href="{{ url('/admins') }}">Admins</a> </li>--}}-->
        <!--                    {{--<li> <a href="{{ url('/clients') }}">Clients</a> </li>--}}-->
        <!--                    {{--<li> <a href="{{ url('/invoices') }}">Invoices</a> </li>--}}-->
        <!--                    {{--<li> <a href="{{ url('/projects') }}">Projects</a> </li>--}}-->
        <!--                {{--</ul>--}}-->
        <!--            {{--</li>--}}-->
            <li><a href="{{ url('/logout') }}" class="waves-effect"><i class="icon-logout fa-fw"></i> <span class="hide-menu">Log out</span></a></li>
        </ul>
    </div>
</div>
<!--add bucket option under left sidebar menu-->
<div id="bucket_dialog" class="modal fade  form-group" role="dialog">
    <div class="modal-dialog">
        <form name="bucket_form" id="bucket_form" action="{{ url('/add-bucket') }}" method="post">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Please provide bucket details</h2>
                    <input type="hidden" id="pass_token" name="_token" value="{{ csrf_token() }}">
                    <div class="row" style="padding: 0 0 10px 10px;">
                        Bucket Name : <input type="text" name="bucket_name" id="bucket_name" class="form-control"><span></span>
                    </div>
                    <div class="row" style="padding: 0 0 10px 10px;">
                        Bucket Region:
                        <select name="bucket_region" id="bucket_region" class="form-control">
                            <option value="">Please select Region</option>
                            <option value="au">Australia (AU)</option>
                            <option value="fr">France (FR)</option>
                            <option value="jp">Japan (JP)</option>
                        </select>
                        <span></span>
                    </div>
                    <div class="row" style="padding: 0 0 10px 10px;">
                        Short Code:
                        <select name="bucket_short_code" id="bucket_short_code" required="" class="form-control">
                            <option value="">Please select short code</option>
                            <option value="ad">AD</option>
                            <option value="af">AF</option>
                            <option value="bc">BC</option>
                            <option value="ys">YS</option>
                        </select>
                        <span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addBucket" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div><!-- /.container-fluid -->

<!--add bucket option under left sidebar menu-->
<?php if($totalBuckets>=100){ ?>
<div id="master_bucket_dialog" class="modal fade form-group" role="dialog">
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
<?php }else{?>
<div id="master_bucket_dialog" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form name="child_bucket_form" id="child_bucket_form" action="{{ url('/add-bucket') }}" method="post">
		<input type="hidden" value="<?php echo $awsId ?>" name ="awsId" id="awsId"/>
            <!-- Modal content-->
            <div class="modal-content form-group">
                <div class="modal-header">
                    <h2>Please provide Bucket details</h2>
                    <input type="hidden" id="pass_token" name="_token" value="{{ csrf_token() }}">
                    <div class="row" style="padding: 0 0 10px 10px;">
                        Master Bucket:
                        <select name="master_child_bucket" id="master_child_bucket" class="form-control">
                            <option value="">Please select bucket</option>
                            @foreach($listMasterBuckets as $bucket)
                                <option value="{{ $bucket->id }}">{{ strtoupper($bucket->bucket_name) }}</option>
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
<?php }?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
        $(document).on('click','#addBucket',function(){
            var bucketName = $('#bucket_name').val();
            var bucketRegion = $('#bucket_region').val();
            var awsId = $('#awsId').val();
            var bucketShortCode = $('#bucket_short_code').val();
            var passToken = $('#pass_token').val();
            var url = '{{ url('/add-bucket') }}';
            var successRedirect = '{{ url('/list-buckets') }}';
            customValid = true;

            var totalError  = [];
            var totalSuccess = [];
            if(bucketName==""){
                totalError.push('bucket_name');
            }else{
                totalSucess.push('bucket_name');
            }
            if(bucketRegion==""){
                totalError.push('bucket_region');
            }else{
                totalSuccess.push('bucket_region');
            }
            if(bucketShortCode==""){
                totalError.push('bucket_short_code');
            }else{
                totalSuccess.push('bucket_short_code');
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
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'bucket_name': bucketName,
                        'awsId': awsId,
                        'bucket_region': bucketRegion,
                        'bucket_short_code': bucketShortCode
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
    });

    //add bucket from MASTER
    $(document).ready(function($){
        $(document).on('click','#addChildBucket',function(){
            var masterBucket = $('#master_child_bucket').val();
           var awsId = $('#awsId').val();
            var passToken = $('#pass_token').val();
            var url = '{{ url('/create-child-bucket') }}';
                    {{--var successRedirect = '{{ url('/list-child-buckets') }}';--}}
            var successRedirect = '{{ url('/buckets') }}';
            customValid = true;

            var totalError  = [];
            var totalSuccess = [];
            if(masterBucket==""){
                totalError.push('master_child_bucket');
            }else{
                totalSuccess.push('master_child_bucket');
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
                        'master_bucket': masterBucket,
                        'awsId': awsId,
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

        //redirect to dashboard page on click from breadcrumbs
        $('.breadcrumb li:first-child a').click(function(){
            var dashboardRedirect = '{{ url('/') }}';
            window.location.href = dashboardRedirect;
        });
    });
</script>
