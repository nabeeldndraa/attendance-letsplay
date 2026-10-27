{{--
    LAYOUT ADMIN
    Memakai kerangka yang sama dengan halaman user (layouts/app.blade.php),
    hanya sidebar/bottom bar dan header-nya diganti versi admin.

    Pemakaian di halaman admin:
        @extends('layouts.admin')
        @section('title', 'Data Presensi')
        @section('content') ... @endsection
--}}
@extends('layouts.app')

@section('sidebar')
    @include('partials.admin-sidebar')
@endsection

@section('header')
    @include('partials.admin-header')
@endsection
