<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Top Navigation -->
<nav class="navbar navbar-default navbar-static-top m-b-0">
    <div class="navbar-header"> <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
        <div class="top-left-part"><a class="logo" href="{{ url('/') }}"><b><!--This is dark logo icon-->
                    <img src="{{ URL::asset('/') }}assests/plugins/images/eliteadmin-logo.png" alt="home" class="dark-logo" /><!--This is light logo icon-->
                    <img src="{{ URL::asset('/') }}assests/plugins/images/eliteadmin-logo-dark.png" alt="home" class="light-logo" /></b></a></div>
        <ul class="nav navbar-top-links navbar-left hidden-xs">
            <li><a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light"><i class="icon-arrow-left-circle ti-menu"></i></a></li>           
        </ul>
        <ul class="nav navbar-top-links navbar-right padding-10">
            <span style="color: rgb(255, 255, 255); margin-right: 7px;">AWS Server</span>
			
            <select style="margin: 8px 12px 0px 0px; width: auto;" name="aws_config" id="configAuth">
                <option value="none">Please select server</option>
                @foreach($configAuth as $config)
                    <?php if(!empty($awsId)){
					 $selected123 = ($awsId == $config->id) ? 'selected="selected"' : '';  ?>
					 
					 <option value="{{$config->id}}" <?php echo $selected123 ?>>{{ $config->aws_name }}</option>
				   <?php }else{?>
                    <?php $selected = ($config->status=='active') ? 'selected="selected"' : ''; ?>
                    <option value="{{ $config->id }}" <?php echo $selected ?>>{{ $config->aws_name }}</option>
				   <?php } ?>
                @endforeach
            </select>
        </ul>

    </div>
</nav>
<script>
    //add bucket name as input and add into DB
    $(document).ready(function($){
        $(document).on('change','#configAuth',function(){
            var configAuthID = $(this).val();
			//alert(configAuthID);
            //var currentActiveID = '{{$activeConfigId}}';
            var currentActiveID = '{{$awsId}}';
			//alert(currentActiveID);
            var url = '{{ url('/activateConfig') }}';
            var successRedirect = '{{ url()->current() }}';
            if(configAuthID=='none'){
                alert("Please select server first!");
                $('#configAuth').val(currentActiveID);
                return false;
            }
            if(confirm("Are you sure to switch the AWS server?")){
                $('#overlay').show();
                $.ajax({
                    type: 'GET',
                    'url': url+'/'+configAuthID,
                    async: false,
                    success:function(data){
                        $('#overlay').hide();
                        var res = jQuery.parseJSON(data);
                        if(res.type=='success'){
                            window.location.href = successRedirect;
                            return false;
                        }
                        if(res.type=='error'){
                            alert(res.message);
                            $('#configAuth').val(currentActiveID);
                            return false;
                        }
                    }
                });
            }else{
                $('#configAuth').val(currentActiveID);
                return false;
            }
        });
    });
</script>