@extends('layouts.dataTable')

@section('page_title', 'Admins')

@section('content')
<?php
$dir = public_path().'/crmbkp/';
$files1 = scandir($dir);
$files = array_diff($files1, array('.', '..'));

$zip_path = base_path().'/public/crmbkp/'
?>
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Backup</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Backup Application</li>
                </ol>
            </div>
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box clearfix">
                    <div class="m-b-10 pull-left">
                        <button type="button" id="backup_btn" class="btn btn-primary btn-sm">Take Database Backup</button>
                        <a href="{{ url('/zipBackup/') }}" title="download" class="btn btn-primary btn-sm">Take Crm BackUp</a>
                    </div>
                    <div class="m-b-10 pull-right">
                        <button type="button" id="delete_file" class="btn btn-primary btn-sm">Bulk Delete</button>
                    </div>
                    <div class="table-responsive col-sm-12">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                <th><input type="checkbox" name="all" id="allchk"></th>
                                <th>No</th>
                                <th>File List</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $counter = 1; 
                            foreach($files as $value){ 
                                $file_path=public_path().'/crmbkp/'.$value;
                                $ext  = (new SplFileInfo($value))->getExtension(); ?>
                                
                                <tr>
                                    <td><input type="checkbox" name="chk" class="chk" value="<?php echo $value; ?>"></td>
                                    <td>{{ $counter}}</td>
                                    <?php if($ext=="sql") { ?>
                                    <td><a href="{{ url('/download-dir/'.$value) }}" title="download"><?php echo $value; ?></a></td>
                                    <?php } else { ?>
                                    <td><a href="{{ url('/crmbkp/'.$value) }}" title="download"><?php echo $value; ?></a></td>
                                    <?php } ?>
                                    <td><a href="{{ url('/deletezip/'.$value.'/') }}" title="delete" class="" onclick="return confirm('Are you sure you want to delete {{ $value }} ?')"><i class="fa fa-trash"></i></a></td>
                                </tr>
                                <?php $counter++; 
                                }?>
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
        var confirmprompt = confirm("Are you sure want to delete this File");
        if(confirmprompt == true){
            $(".ajax_loader").css("display", "block");
            var name = $(this).data('name');
            $.ajax({
                type: "POST",
                url: '{{url("/deletezip/")}}',
                data: {filename:name,"_token": "{{ csrf_token() }}"},
                success: function( msg ) {
                  $(".ajax_loader").css("display", "none");
                  alert("File Deleted Successfully");
                  window.location.reload(true);
                }
            });
        }else{
            return false;
        }
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
        if(arr.length > 0){
            var url = '{{ url('/delete-multiple-files') }}';
            var passToken = "{{ csrf_token() }}";
            var successRedirect = '{{ url('/script-backup') }}';
            if(confirm("Are you sure to delete Files?")){
                $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'file_names': arr
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
