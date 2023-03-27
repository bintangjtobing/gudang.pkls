@extends('layouts.main')
@section('title', __('Users'))
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
        <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-user" onclick="addUser()"><i class="fas fa-plus"></i> Add New User</button>
        </div>
        <div class="card-body">
            <table id="table" class="table table-sm table-bordered table-hover table-striped">
            <thead>
                <tr class="text-center">
                    <th>No.</th>
                    <th>{{ __('Fullname') }}</th>
                    <th>{{ __('Username') }}</th>
                    <th>{{ __('Role') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @if(count($users) > 0)
                @foreach($users as $key => $d)
                @php
                    $data = ["user_id" => $d->id, "fullname" => $d->name, "username" => $d->username, "role" => $d->role];
                    if($d->role == 0){
                        $role = "Admin";
                    } else {
                        $role = "User";
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $users->firstItem() + $key }}</td>
                    <td>{{ $data['fullname'] }}</td>
                    <td>{{ $data['username'] }}</td>
                    <td>{{ $role }}</td>
                    <td class="text-center"><button title="Edit User" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-user" onclick="editUser({{ json_encode($data) }})"><i class="fas fa-edit"></i></button> <button title="Hapus User" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-user" onclick="deleteUser({{ json_encode($data) }})"><i class="fas fa-trash"></i></button></td>
                </tr>
                @endforeach
            @else
                <tr class="text-center">
                    <td colspan="4">{{ __('No user.') }}</td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>
        </div>
        <div>
        {{ $users->links("pagination::bootstrap-4") }}
        </div>
    </div>
    <div class="modal fade" id="add-user">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Add New User') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="update" action="{{ route('users.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="form-group row">
                            <label for="username" class="col-sm-4 col-form-label">{{ __('Username') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fullname" class="col-sm-4 col-form-label">{{ __('Name') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="fullname" name="fullname">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-4 col-form-label">{{ __('Password') }}</label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="role" class="col-sm-4 col-form-label">Role</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" style="width: 100%;" id="role" name="role">
                                    <option value="">.:: Select Role ::.</option>
                                    <option value="0">Admin</option>
                                    <option value="1">User</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-save" type="button" class="btn btn-primary" onclick="$('#update').submit();">{{ __('Add') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-user">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Hapus User') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('users.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                        <input type="hidden" id="delete_role" name="delete_role">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus user <span id="delete_name" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-delete" type="button" class="btn btn-danger" onclick="$('#delete').submit();">{{ __('Ya, hapus') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('custom-js')
    <script>
        function resetForm(){
            $('#update').trigger("reset");
            $('#user_id').val('');
        }

        function addUser(){
            resetForm();
            $('#modal-title').text("Add New User");
            $('#button-save').text("Add");
        }

        function editUser(data){
            resetForm();
            $('#modal-title').text("Edit User");
            $('#button-save').text("Simpan");
            $('#user_id').val(data.user_id);
            $('#fullname').val(data.fullname);
            $('#username').val(data.username);
            $('#role').val(data.role);
        }

        function deleteUser(data){
            $('#delete_id').val(data.user_id);
            $('#delete_name').text(data.username);
            $('#delete_role').val(data.role);
        }
    </script>
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