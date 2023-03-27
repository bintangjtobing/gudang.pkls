@extends('layouts.main')
@section('title', __('Product Categories'))
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
                @if(Auth::user()->role == 0)
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-category" onclick="addCategory()"><i class="fas fa-plus"></i> Add New Category</button>
                @endif
                <div class="card-tools">
                    <form>
                        <div class="input-group input-group">
                            <input type="text" class="form-control" name="q" placeholder="Search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table id="table" class="table table-sm table-bordered table-hover table-striped">
                <thead>
                    <tr class="text-center">
                        <th>No.</th>
                        <th>{{ __('Category Name') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @if(count($categories) > 0)
                    @foreach($categories as $key => $d)
                    @php
                        $data = ["category_id" => $d->category_id, "category_name" => $d->category_name];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $categories->firstItem() + $key }}</td>
                        <td>{{ $data['category_name'] }}</td>
                        <td class="text-center"><button title="Lihat Produk Untuk Kategori Ini" type="button" class="btn btn-primary btn-xs" onclick="window.location.href='/products?category={{ $d->category_id }}'"><i class="fas fa-external-link-alt"></i></button> @if(Auth::user()->role == 0)<button title="Edit Shelf" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-category" onclick="editCategory({{ json_encode($data) }})"><i class="fas fa-edit"></i></button> <button title="Hapus Produk" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-category" onclick="deleteCategory({{ json_encode($data) }})"><i class="fas fa-trash"></i></button>@endif</td>
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
        {{ $categories->links("pagination::bootstrap-4") }}
        </div>
    </div>
    @if(Auth::user()->role == 0)
    <div class="modal fade" id="add-category">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Add New Category') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save" action="{{ route('products.categories.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="category_id" name="category_id">
                        <div class="form-group row">
                            <label for="category_name" class="col-sm-4 col-form-label">{{ __('Category Name') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="category_name" name="category_name">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-save" type="button" class="btn btn-primary" onclick="$('#save').submit();">{{ __('Add') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-category">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Delete Category') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('products.categories.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus kategori <span id="delete_name" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                    <button id="button-save" type="button" class="btn btn-danger" onclick="$('#delete').submit();">{{ __('Ya, hapus') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection
@section('custom-js')
    <script>
        function resetForm(){
            $('#save').trigger("reset");
            $('#category_id').val('');
        }

        function addCategory(){
            resetForm();
            $('#modal-title').text("Add New Category");
            $('#button-save').text("Add");
        }

        function editCategory(data){
            resetForm();
            $('#modal-title').text("Edit Category");
            $('#button-save').text("Simpan");
            $('#category_id').val(data.category_id);
            $('#category_name').val(data.category_name);
        }

        function deleteCategory(data){
            $('#delete_id').val(data.category_id);
            $('#delete_name').text(data.category_name);
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