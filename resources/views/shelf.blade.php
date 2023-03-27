@extends('layouts.main')
@section('title', __('Shelf'))
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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-shelf" onclick="addShelf()"><i class="fas fa-plus"></i> Add New Shelf</button>
        </div>
        <div class="card-body">
            <table id="table" class="table table-sm table-bordered table-hover table-striped">
            <thead>
                <tr class="text-center">
                    <th>No.</th>
                    <th>{{ __('Shelf Name') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @if(count($shelf) > 0)
                @foreach($shelf as $key => $d)
                @php
                    $data = ["shelf_id" => $d->shelf_id, "shelf_name" => $d->shelf_name];
                @endphp
                <tr>
                    <td class="text-center">{{ $shelf->firstItem() + $key }}</td>
                    <td>{{ $data['shelf_name'] }}</td>
                    <td class="text-center"><button title="Edit Shelf" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-shelf" onclick="editShelf({{ json_encode($data) }})"><i class="fas fa-edit"></i></button> <button title="Hapus Shelf" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-shelf" onclick="deleteShelf({{ json_encode($data) }})"><i class="fas fa-trash"></i></button></td>
                </tr>
                @endforeach
            @else
                <tr class="text-center">
                    <td colspan="3">{{ __('No data.') }}</td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>
        </div>
        <div>
        {{ $shelf->links("pagination::bootstrap-4") }}
        </div>
    </div>
    <div class="modal fade" id="add-shelf">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Add New Shelf') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="update" action="{{ route('products.shelf.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="shelf_id" name="shelf_id">
                        <div class="form-group row">
                            <label for="shelf_name" class="col-sm-4 col-form-label">{{ __('Name') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="shelf_name" name="shelf_name">
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
    <div class="modal fade" id="delete-shelf">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Hapus Shelf') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('products.shelf.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                    </form>
                    <div>
                        <p class="text-danger">Perhatian! Stok serta history yang berada di shelf ini juga akan ikut terhapus!</p>
                        <p>Anda yakin ingin tetap menghapus shelf <span id="delete_name" class="font-weight-bold"></span>?</p>
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
            $('#shelf_id').val('');
        }

        function addShelf(){
            resetForm();
            $('#modal-title').text("Add New Shelf");
            $('#button-save').text("Add");
        }

        function editShelf(data){
            resetForm();
            $('#modal-title').text("Edit Shelf");
            $('#button-save').text("Simpan");
            $('#shelf_id').val(data.shelf_id);
            $('#shelf_name').val(data.shelf_name);
        }

        function deleteShelf(data){
            $('#delete_id').val(data.shelf_id);
            $('#delete_name').text(data.shelf_name);
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