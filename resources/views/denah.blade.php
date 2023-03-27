@extends('layouts.main')
@section('title', __('Denah'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('content')
<div class="content-header">
    <div class="container-fluid">
    <div class="row mb-2">
    </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
        	@php
        	$warna = ['bg-success', 'bg-info','bg-warning','bg-primary']
        	@endphp
        	@foreach($shelf as $val)
            <div class="col-lg-3 col-6">
                <a href="{{ route('products.denah.detail', $val->shelf_id) }}" >
                    <div class="small-box {{ $warna[rand(0,3)] }}">
                        <div class="inner">
                        	<p>Rak {{ $loop->index+1 }}</p>
                        	<h5>{{ $val->shelf_name }}</h5>
                            {{-- <h3>{{ $val->shelf_name }}</h3> --}}
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

</section>
@endsection
@section('custom-js')
    <script src="/plugins/toastr/toastr.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    @if(Session::has('success'))
        <script>toastr.success('{!! Session::get("success") !!}');</script>
    @endif
    @if(Session::has('error'))
        <script>toastr.error('{!! Session::get("error") !!}');</script>
    @endif
    @if(!empty($errors->all()))
        <script>toastr.error('{!! implode("", $errors->all("<li>:message</li>")) !!}');</script>
    @endif
@endsection