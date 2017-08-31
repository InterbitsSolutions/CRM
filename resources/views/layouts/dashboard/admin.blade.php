<div id="dashboardOverlay">
    <p>Please wait while processing...<img src="{{ URL::asset('/') }}assests/plugins/images/loading.gif" alt="home"></p>
</div>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">CRM Dashboard Page</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">CRM Dashboard</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!--row -->
    <div class="row">
        <div class="col-md-6 col-sm-6">
            <a href="{{ url('/list-master-buckets') }}">
                <div class="master-bucket-box">
                    <span class="bucket-master">Master Buckets</span>
                    <div class="clearfix box-inner">
                        <div class="r-icon-stats">
                            <i class="fa fa-bitbucket"></i>
                            <div class="bodystate">
                                <h4 id="masterCount">{{ $masterBucketCount  }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-sm-6">
            <a href="{{ url('/buckets') }}">
                <div class="bucket-box">
                    <span class="bucket">Buckets</span>
                    <div class="clearfix box-inner">
                        <div class="r-icon-stats">
                            <i class="fa fa-bitbucket"></i>
                            <div class="bodystate">
                                <h4 id="totalCount">{{ $totalBucketCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row" id="graphContainer" style="display: none;">
        <div class="col-md-6 col-sm-6" id="bucketHTML">
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Buckets by Network</h3>
                <div id="bucket-donut-chart" class="ecomm-donute" style="height: 317px;"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // call function to manage the Dashboard graphs and other values
        $(function() {
            setTimeout(loadDashboardContent, 300);
        });
        function loadDashboardContent() {
            $.ajax({
                type: "POST",
                url: '{{url("/get-dashboard-content/")}}',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(result) {
                    var dashboardContent = jQuery.parseJSON(result);
                    if(dashboardContent.type=='success'){
                        var masterCount = dashboardContent.masterBuckets;
                        var totalBuckets = dashboardContent.totalBuckets;
                        $('#masterCount').html(masterCount);
                        $('#totalCount').html(totalBuckets);

                        //LEFT graph
                        var bucketHTML = dashboardContent.bucketHTML;
                        $('#bucketHTML').html(bucketHTML);

                        //Donut graph
                        var awsBucketData = dashboardContent.awsBucketData;
                        $('#graphContainer').show();
                        var graphData = [];
                        for (key = 0; key < awsBucketData.length; key++) {
                            graphData[key] = {
                                value: awsBucketData[key].value,
                                label: awsBucketData[key].label
                            }
                        }
                        Morris.Donut({
                            element: 'bucket-donut-chart',
                            data: graphData,
                            resize: true,
                            colors: ['#fb9678', '#01c0c8', '#4F5467', '#00c292', '#03a9f3', '#ab8ce4', '#13dafe', '#99d683', '#B4C1D7']
                        });
                        $('#dashboardOverlay').hide();
                        return false;
                    }
                    if(dashboardContent.type=='error'){
                        alert('There is some error, please refresh the page');
                        return false;
                    }
                }
            });
        }
    });
</script>