@extends('layouts.adminDashboard')

@section('page_title', 'Home')

@section('content')
    @if($user->isAdmin())
        @include('layouts.dashboard.admin')
    @else
        @include('layouts.dashboard.client')
    @endif
@endsection
