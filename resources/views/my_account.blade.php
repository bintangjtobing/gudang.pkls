@extends('layouts.main')
@section('title', __('My Account'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
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
            <div class="col-md-5 mx-auto">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Profile</h3>
                    </div>
                    <div class="card-body">
                        <form role="form" id="update" action="{{ route('myaccount.update') }}" method="post">
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">{{ __('Username') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="username" value="{{ Auth::user()->username }}" disabled>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="fullname" class="col-sm-4 col-form-label">{{ __('Fullname') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="fullname" name="fullname" value="{{ Auth::user()->name }}">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button id="button-update" type="button" class="btn btn-primary float-right" onclick="$('#update').submit();">{{ __('Update Profile') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 mx-auto">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Password</h3>
                    </div>
                    <div class="card-body">
                        <form role="form" id="update_password" action="{{ route('myaccount.updatePassword') }}" method="post">
                            @csrf
                            <div class="form-group row">
                                <label for="current_password" class="col-sm-4 col-form-label">{{ __('Current Password') }}</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="new_password" class="col-sm-4 col-form-label">{{ __('New Password') }}</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="new_password_confirmation" class="col-sm-4 col-form-label">{{ __('Confirm Password') }}</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button id="button-update" type="button" class="btn btn-primary float-right" onclick="$('#update_password').submit();">{{ __('Change Password') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('custom-js')
    <script src="/plugins/toastr/toastr.min.js"></script>
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