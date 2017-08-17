@extends('layouts.dataTable')

@section('page_title', 'Admins')

@section('content')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Manage Hit List</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Network Hits</li>
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
                                <th>Network Name</th>
                                <th>Customer IP</th>
                                <th>City</th>
                                <th>Browser</th>
                                <th>Date</th>
                                <th>Hits</th>
								
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            
                            $counter = 1;
                            ?>
                            @foreach($networkHits as $hit)
                                
                                <tr>
                                    <td>{{ $counter}}</td>
                                    <td>{{ strtoupper($hit->bucket_name) }}</td>
                                    <td>{{ $hit->customer_ip }}</td>
									<td>{{ $hit->city }}</td>
									<td>{{ $hit->browser }}</td>
									<td>{{ (!empty($hit->created_at)) ? date("d-m-Y",strtotime($hit->created_at)) : '-' }}</td>
                                    <td>{{ $hit->hits }}</td>
                                    
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