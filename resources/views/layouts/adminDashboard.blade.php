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
