@extends('layouts.dataTable')
@section('page_title', 'Users')
@section('content')
<!-- Page Content -->
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">View User</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">View User</li>
            </ol>
        </div>
    </div>
    <!-- /row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="table-responsive">
                    <table id="example23" class="table table-striped table_grid">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            @if (array_key_exists($user->role,$roles))
                            <tr>
                                <td>{{ (!empty($user->name)) ? $user->name : "-" }}</td>
                                <td>{{ (!empty($user->email)) ? $user->email : "-"  }}</td>
                                <td>{{ (!empty($user->role)) ? $roles[$user->role] : "-"  }}</td>
                                <td class="record_actions">
                                    @if($user->role == 1 && $login_user_id > 1)
                                    <a href="{{ url('/edit-user/'.$user->id) }}" title="edit" class="btn btn-primary-btn" disabled><i class="fa fa-edit"></i></a>
                                    @elseif($user->role == 1 && $login_user_id == 1)
                                    <a href="{{ url('/edit-user/'.$user->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                    @else
                                    <a href="{{ url('/edit-user/'.$user->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                    @endif	
                                    <a data-toggle="modal" data-target="#update_password_dialog" title="Update Password" class="btn btn-primary" onclick="$('#userid').val( {{ $user->id }} );">Update Password</a>
                                    @if($user->id == 1 || ($user->role == 1 && $login_user_id > 1))
                                    <a href="{{ url('/delete-user/'.$user->id) }}" title="delete" class="btn btn-danger1" disabled><i class="fa fa-trash"></i></a>  
                                    @else
                                    <a href="{{ url('/delete-user/'.$user->id) }}" title="delete" class="btn btn-danger1" onclick="return confirm('Are you sure you want to delete {{ $user->main_url }} ?')"><i class="fa fa-trash"></i></a>  
                                    @endif 
                                </td>
                            </tr>                                
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</div>
<!--- update password -->
<div id="update_password_dialog" class="modal fade form-group " role="dialog">
    <div class="modal-dialog">
        <form name="form_update_password" id="form_update_password" method="post">
            <input type="hidden" id="dpass_token_aws" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="userid" name="user_id" value="">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h2>New Password:</h2>
                    <input type="password" name="new_password" id="new_password" required class="form-control" oninput="form.confirm.pattern = escapeRegExp(this.value)">
                    <h2>Confirm Password:</h2>
                    <input type="password" name="c_password" id="c_password" required class="form-control">
                    <p style="color:red;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" name="upd_password" id="upd_password" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- update password -->
<?php $searchValue = (!empty($_GET['x'])) ? $_GET['x'] : ''; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
$(window).load(function() {
    var searchItemValue = '<?php echo $searchValue; ?>';
    $('div.dataTables_filter input').val(searchItemValue).trigger('keyup');
});
</script>
<script>
    $(document).on('click', '#upd_password', function(){
        var passToken = $('#dpass_token_aws').val();
        var userid = $('#userid').val();
        var password = $('#new_password').val();
        var c_password = $('#c_password').val();
        var url = '{{ url('/change-password') }}';
        var successRedirect = '{{ url('/list-users') }}';
        if (password != c_password){
            alert("Confirm Password should be same as Password");
            $('#new_password').val('');
            $('#c_password').val('');
            return false;
        } else{
            $.ajax({
                    type: 'POST',
                    'url': url,
                    async: false,
                    data: {
                        '_token': passToken,
                        'userid': userid,
                        'password': password,
                    },
                    success:function(data){
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
    });

</script>

@endsection


