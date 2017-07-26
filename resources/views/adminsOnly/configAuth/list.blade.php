@extends('layouts.dataTable')

@section('page_title', 'Admins')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Manage Configurations</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Configurations</li>
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
                                <th>No</th>
                                <th>Aws Server Name</th>
                                <th>Key</th>
                                <th>Secret</th>
                                <th>Initial Counter</th>
                                <th>Is Primary Network?</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $regionArray = array('jp'=>'Japan (Tokyo)', 'fr'=>'France', 'au'=>'Australia') ;
                            $counter = 1;
                            ?>
                            @foreach($configAuth as $config)
                                <?php
                                    //status
                                    if($config->status=='active'){
                                    $statusOptions = '<a href="#" style="color:green;"><b>Active</b></a> /  <span>In-active</span>';
                                    }else{
                                        $statusOptions = '<a href="'.url('/config/'.$config->id.'/active/').'" style="color:green;">Active</a> /  <a href="'.url('/config/'.$config->id.'/inactive/').'" style="color:red;">In-active</a>';
                                    }
                                    //primary network
                                    if($config->primary_network=='yes'){
                                        $primaryNetwork = '<a href="#" style="color:green;"><b>Yes</b></a> /  <span>No</span>';
                                    }else{
                                        $primaryNetwork = '<a href="'.url('/primary/'.$config->id.'/active/').'" style="color:green;">Yes</a> /  <a href="'.url('/primary/'.$config->id.'/inactive/').'" style="color:red;">No</a>';
                                    }
                                ?>
                                <tr>
                                    <td>{{ $counter}}</td>
                                    <td>{{ $config->aws_name }}</td>
                                    <td>{{ $config->key }}</td>
                                    <td>{{ $config->secret }}</td>
                                    <td>{{ (!empty($config->aws_counter)) ? $config->aws_counter : '-' }}</td>
                                    <td><?php echo $primaryNetwork; ?></td>
                                    <td><?php echo $statusOptions; ?></td>
                                    <td class="record_actions">
                                        <a href="{{ url('/config/'.$config->id.'/edit/') }}" title="edit" class=""><i class="fa fa-edit"></i></a>
                                        <a href="{{ url('/config/'.$config->id.'/delete/') }}" title="delete" class="" onclick="return confirm('Are you sure you want to delete {{ $config->key }} ?')"><i class="fa fa-trash"></i></a>
                                    </td>
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