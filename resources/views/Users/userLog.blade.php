@extends('layouts.dataTable')

@section('page_title', 'User Log')

@section('content')
<!-- Page Content -->
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">User Log</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">User Log</li>
            </ol>
        </div>
    </div>
    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box clearfix">
                <div class="m-b-10 pull-left">
                    <button type="button" id="bulk_delete" class="btn btn-primary btn-sm">Bulk Delete</button>

                </div>
                <div class="m-b-10 pull-right">

                </div>
                <div class="table-responsive col-sm-12">
                    <table id="example23" class="table table-striped table_grid">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="all" id="allchk"></th>
                                <th>Sl.No</th>
                                <th>User Name</th>
                                <th>Aws Server</th>
                                <th>Bucket Name</th>
                                <th>Action Performed</th>
                                <th>Action Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            @foreach($userDetails as $userdetail)                              
                            <tr>
                                <td><input type="checkbox" name="chk" class="chk" value="<?php echo $userdetail->id; ?>"></td>
                                <td>{{ $counter}}</td>
                                <td>{{ (!empty($userdetail->name)) ? $userdetail->name : "-" }}</td>
                                <td>{{ (!empty($userdetail->aws_name)) ? $userdetail->aws_name : "-"  }}</td>
                                <td>{{ (!empty($userdetail->bucket_name)) ? $userdetail->bucket_name : "-"  }}</td>
                                <td>{{ (!empty($userdetail->action_performed)) ? $userdetail->action_performed : "-"  }}</td>
                                <td>{{ (!empty($userdetail->created)) ? $userdetail->created : "-"  }}</td>
                                <td><a href="{{ url('/deletelog/'.$userdetail->id.'/') }}" title="delete" class="" onclick="return confirm('Are you sure you want to delete {{ $userdetail->name }} ?')"><i class="fa fa-trash"></i></a></td>
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


@endsection
<div class="ajax_loader" style="display:none"><center><img src="http://2.bp.blogspot.com/-5HefTzohwJs/VPz4EEX3IkI/AAAAAAAANd8/lociel7unYg/s1600/11-01%2B~%2BGIF%2B~%2BPlease%2BWait.gif"></center></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '#allchk', function(){
        $(".chk").prop('checked', $(this).prop('checked'));
    });
    $(document).on('click', '#bulk_delete', function(){
        var arr = [];
        $('input.chk:checkbox:checked').each(function () {
            arr.push($(this).val());
        });
        if (arr.length === 0) {
            alert("Please Select At lest one Log"); return false;
        }
        if (arr.length > 0){
            var url = '{{ url('/delete-multiple-log') }}';
            var passToken = "{{ csrf_token() }}";
            var successRedirect = '{{ url('/user-log') }}';
            if (confirm("Are you sure to delete Logs?")){
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                    '_token': passToken,
                            'logs': arr
                    },
                    success:function(data){
                        $('#overlay').hide();
                        var res = jQuery.parseJSON(data);
                        if (res.type == 'success'){
                            window.location.href = successRedirect;
                            return false;
                        }
                        if (res.type == 'error'){
                            alert(res.message);
                            return false;
                        }
                    }
                });
            }
        }
    });
});
</script>


<style>
    .ajax_loader{
        position:absolute;
        width:100%;
        height:100%;
        left:0;
        top:50px;
        background:rgba(0,0,0,.5);
        z-index: 9999;
    }
    .ajax_loader i{
        position:absolute;    
        left:50%;
    }
    .top-left-part{
        height:50px !important;
    }
</style>

