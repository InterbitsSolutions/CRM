<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('crm.site_title') }} - @yield('page_title')</title>
    <!-- Bootstrap Core CSS -->
    <link href="{{ URL::asset('/') }}assests/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Menu CSS -->
    <link rel="stylesheet" href="{{ URL::asset('/') }}assests/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css" />
    <!-- morris CSS -->
    <link href="{{ URL::asset('/') }}assests/plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- animation CSS -->
    <link rel="stylesheet" href="{{ URL::asset('/') }}css/animate.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ URL::asset('/') }}css/style.css" />
    <!-- color CSS -->
    <link href="{{ URL::asset('/') }}css/colors/gray-dark.css" id="theme"  rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript" language="javascript">
$(document).ready(function() {
    $('.sidebar').css({
        'position': 'fixed'               
    });
    $('.navbar-header').css({
            'position': 'fixed'               
    });
    function is_valid_url(url) 
    {
        return /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(url);
    }
    $(document).on("keyup blur",'input[name="bucket_phone_number"]',function(){
         var $td = $(this);
        $td.val( $td.val().replace(/[^0-9- +]/g, function(str) { return ''; } ) );
    });
    $(document).on("keyup blur",'input[name="field_name"]',function(){
        var $th = $(this);
        $th.val( $th.val().replace(/[^a-zA-Z!@#$&()\\-`.+,/\%* ]/g, function(str) { return ''; } ) );
    });
    $(document).on("keyup blur",'input[name="aws_counter"]',function(){
        var $intialc = $(this);
        $intialc.val( $intialc.val().replace(/[^0-9]/g, function(str) { return ''; } ) );
    });
    
    $(document).on("blur","#main_url,#cloak_url,#safe_url",function(){
        var url = $(this).val();
        var validurl = is_valid_url(url);
        if(validurl == false){
            $(this).val('');
           // alert("Please Provide a valid Url");
        }

    });   
    $(document).on('click','#backup_btn',function(){
        $(".ajax_loader").css("display", "block");
        $.ajax({
            type: "POST",
            url: '{{url("/crmbackup/")}}',
            data: {title:'backup',"_token": "{{ csrf_token() }}"},
            success: function( msg ) {
              $(".ajax_loader").css("display", "none");
              window.location.reload(true);
            }
        });
    });
    
    $(document).on('click','.deletezip',function(){
        $(".ajax_loader").css("display", "block");
        var name = $(this).data('name');
        $.ajax({
            type: "POST",
            url: '{{url("/deletezip/")}}',
            data: {filename:name,"_token": "{{ csrf_token() }}"},
            success: function( msg ) {
              $(".ajax_loader").css("display", "none");
              window.location.reload(true);
            }
        });
    });
    $(document).on('click','#allchk',function(){
         $(".chk").prop('checked', $(this).prop('checked'));
    });
    $(document).on('click','#delete_file',function(){
        var arr = [];
        $('input.chk:checkbox:checked').each(function () {
            arr.push($(this).val());
        });
        if (arr.length === 0) {
            alert("Please Select At lest one File");return false;
        }
        $.ajax({
            type: "POST",
            url: '{{url("/deletezip/")}}',
            data: {type:'deletechecked',filenames:arr,"_token": "{{ csrf_token() }}"},
            success: function( msg ) {
              $(".ajax_loader").css("display", "none");
              alert("Selected Files are Deleted");
              window.location.reload(true);
            }
        });

    });
    $(document).on('keyup','#user_role_name',function(){
        var hidden_role_name = $('#hidden_role_name').val();
        var role_name = $(this).val();
        if(role_name != hidden_role_name){
            
        $.ajax({
            type: "POST",
            url: '{{url("/check-duplicate-role/")}}',
            data: {role_name:role_name,"_token": "{{ csrf_token() }}"},
            success: function(result) {
              if(result > 0){
                alert("Role already Exist. Please create a different Role");
                $('#user_role_name').val('');
                return false;
              }
            }
        });
    }
    if(hidden_role_name == ""){
        
        $.ajax({
            type: "POST",
            url: '{{url("/check-duplicate-role/")}}',
            data: {role_name:role_name,"_token": "{{ csrf_token() }}"},
            success: function(result) {
              if(result > 0){
                alert("Role already Exist. Please create a different Role");
                $('#user_role_name').val('');
                return false;
              }
            }
        });
    }
    });
    $(document).on('click','.role_save,.role_update',function(){
        var num_checked = $("input[name='modules[]']:checked").length;
        if(num_checked < 1){
            alert("Please Select at least one Module");
            return false;
        }else{
            return true;
        }
    });

});




$(document).ready(function(){
    $('.alert.alert-success').fadeOut(10000);
    $('.alert.alert-danger').fadeOut(10000);
});

</script>

<!-- <INPUT id="txtChar" onkeypress="return isNumberKey(event)" type="text" name="txtChar"> -->


    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <div id="overlay" style="display: none;">
        <p>Please wait while processing...</p>
    </div>
    <div id="wrapper">
        <!-- Header -->
        @include('layouts.dashboard.header')
        <!-- Left navbar-header -->
        @include('layouts.dashboard.leftbar')
        <!-- Page Content -->
        <div id="page-wrapper">
            @if (session('status'))
                <div class="alert alert-{{ session('status_level') ?: "success" }}">
                    <div>{{ session('status') }}</div>
                </div>
            @endif
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                        @endforeach

                </div>
            @endif
            @yield('content')
        </div>
        <!-- Footer -->
        @include('layouts.dashboard.footer')
    </div>
    <!-- jQuery -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{{ URL::asset('/') }}bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <!--slimscroll JavaScript -->
    <script src="{{ URL::asset('/') }}js/jquery.slimscroll.js"></script>
    <!--Morris JavaScript -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/raphael/raphael-min.js"></script>
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/morrisjs/morris.js"></script>
    <!-- Sparkline chart JavaScript -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/jquery-sparkline/jquery.sparkline.min.js"></script>
    <!-- jQuery peity -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/peity/jquery.peity.min.js"></script>
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/peity/jquery.peity.init.js"></script>
    <!--Wave Effects -->
    <script src="{{ URL::asset('/') }}js/waves.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{{ URL::asset('/') }}js/custom.min.js"></script>
    <script src="{{ URL::asset('/') }}js/dashboard1.js"></script>
    <!--Style Switcher -->
    <script src="{{ URL::asset('/') }}assests/plugins/bower_components/styleswitcher/jQuery.style.switcher.js"></script>
    @yield('footer')
    
    


</body>
</html>
