@extends('layouts.main')
@section('title', __('Warehouse'))
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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-warehouse" onclick="addWarehouse()"><i class="fas fa-plus"></i> Add New Warehouse</button>
        </div>
        <div class="card-body">
            <table id="table" class="table table-sm table-bordered table-hover table-striped">
            <thead>
                <tr class="text-center">
                    <th>No.</th>
                    <th>{{ __('Name') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @if(count($warehouse) > 0)
                @foreach($warehouse as $key => $d)
                @php
                    $data = ["warehouse_id" => $d->warehouse_id, "warehouse_name" => $d->warehouse_name];
                @endphp
                <tr>
                    <td class="text-center">{{ $warehouse->firstItem() + $key }}</td>
                    <td>{{ $data['warehouse_name'] }}</td>
                    <td class="text-center"><button title="Edit" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-warehouse" onclick="editWarehouse({{ json_encode($data) }})"><i class="fas fa-edit"></i></button> <button title="Hapus" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-warehouse" onclick="deleteWarehouse({{ json_encode($data) }})"><i class="fas fa-trash"></i></button></td>
                </tr>
                @endforeach
            @else
                <tr class="text-center">
                    <td colspan="4">{{ __('No Warehouse.') }}</td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>
        </div>
        <div>
        {{ $warehouse->links("pagination::bootstrap-4") }}
        </div>
    </div>
    <div class="modal fade" id="add-warehouse">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Add New Warehouse') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="update" action="{{ route('warehouse.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="warehouse_id" name="warehouse_id">
                        <div class="form-group row">
                            <label for="name" class="col-sm-4 col-form-label">{{ __('Warehouse Name') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="name" name="name">
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
    <div class="modal fade" id="delete-warehouse">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Hapus') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('warehouse.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus warehouse <span id="delete_name" class="font-weight-bold"></span>?</p>
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
            $('#warehouse_id').val('');
        }

        function addWarehouse(){
            resetForm();
            $('#modal-title').text("Add New Warehouse");
            $('#button-save').text("Add");
        }

        function editWarehouse(data){
            resetForm();
            $('#modal-title').text("Edit");
            $('#button-save').text("Simpan");
            $('#warehouse_id').val(data.warehouse_id);
            $('#name').val(data.warehouse_name);
        }

        function deleteWarehouse(data){
            $('#delete_id').val(data.warehouse_id);
            $('#delete_name').text(data.warehouse_name);
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