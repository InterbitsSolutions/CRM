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
                                <h4>{{ $masterBucketCount  }}</h4>
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
                                <h4>{{ $totalBucketCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6">
        <?php
        if(!empty($data)){
        rsort($data);
        foreach($data as $dataKey => $dataVal) {
//                    $dataKey = ($dataKey!=""?$dataKey:"N/A");
                $dataName = ($dataVal[0]!=""?$dataVal[0]:"N/A");
                $bucketCounter = count($dataVal);
                $counterClass = ($bucketCounter>10) ? "bucket-count count10" : "bucket-count";
                $bucketClass = ($bucketCounter>10) ? "bucket-icon bucket10" : "bucket-icon";
            ?>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                    <!--<a href="{{ url('/buckets?type=dashboard&x=').$dataName }}">-->
					<a href="{{ url('/buckets?type=dashboard&x=').$dataName .('&bcnt=').$bucketCounter}}">
                        <div class="bucket_bg_value">
                            <span class="{{$counterClass}}">{{ $bucketCounter }}</span>
                            <div class="{{$bucketClass}}"><i class="fa fa-shopping-basket fa-2x"></i></div>
                            <div class="text-bucket">{{ strtoupper($dataName) }}</div>
                        </div>
                    </a>
                </div>
        <?php } } ?>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
            <div class="white-box">
                <h3 class="box-title">Buckets by Network</h3>
                <div id="bucket-donut-chart" class="ecomm-donute" style="height: 317px;"></div>
                <ul class="list-inline m-t-30 text-center">
                    <?php $counter = 0; ?>
                    @foreach($awsBucketsArr as $awsBucketData)
                        <li class="p-r-20">
                            <h5 class="text-muted"><i class="fa fa-circle" style="color:{{$graphColorCodes[$counter]}};"></i> {{ $awsBucketData['label'] }}</h5>
                            <h4 class="m-b-0">{{ $awsBucketData['value'] }}</h4>
                        </li>
                    <?php $counter++; ?>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        var awsBucketData = <?php echo json_encode($awsBucketsArr);?>;
        Morris.Donut({
            element: 'bucket-donut-chart',
            data:awsBucketData,
            resize: true,
            colors:['#fb9678', '#01c0c8', '#4F5467', '#00c292', '#03a9f3', '#ab8ce4', '#13dafe', '#99d683', '#B4C1D7']
        });
    });
    function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }
    function random_color() {
        return random_color_part() . random_color_part() . random_color_part();
    }
</script>
