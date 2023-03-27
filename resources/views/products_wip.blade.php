@extends('layouts.main')
@section('title', __('Work In Progress (WIP)'))
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
		<div class="card">
			<div class="card-header">
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-product" onclick="addProduct()"><i class="fas fa-plus"></i> Add New WIP</button>
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
				<div class="table-responsive">
					<table id="table" class="table table-sm table-bordered table-hover table-striped">
						<thead>
							<tr class="text-center">
								<th>No.</th>
								<th>{{ __('Product Code') }}</th>
								<th>{{ __('Product Name') }}</th>
								<th>{{ __('Amount') }}</th>
								<th>{{ __('Date In') }}</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@if(count($products) > 0)
							@foreach($products as $key => $d)
							@php
							$data = [
								"no"        => $products->firstItem() + $key,
								"pid"       => $d->product_wip_id,
								"pcode"     => $d->product_code,
								"pname"     => $d->product_name,
								"pamount"   => $d->product_amount,
                                            // "pshelf"   => $d->Shelf_id,
								"date_in"   => date("d/m/Y H:i:s", strtotime($d->date_in)),
							];
							@endphp
							<tr>
								<td class="text-center">{{ $data['no'] }}</td>
								<td class="text-center">{{ $data['pcode'] }}</td>
								<td>{{ $data['pname'] }}</td>
								<td class="text-center">{{ $data['pamount'] }}</td>
								<td class="text-center">{{ $data['date_in'] }}</td>
								<td class="text-center"><button title="Selesai" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#wip-complete" onclick="wipComplete({{ json_encode($data) }})"><i class="fas fa-check"></i></button> <button title="Edit" type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#add-product" onclick="editProduct({{ json_encode($data) }})"><i class="fas fa-edit"></i></button> <button title="Hapus" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-product" onclick="deleteProduct({{ json_encode($data) }})"><i class="fas fa-trash"></i></button></td>
							</tr>
							@endforeach
							@else
							<tr class="text-center">
								<td colspan="8">{{ __('No data.') }}</td>
							</tr>
							@endif
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div>
			{{ $products->appends(request()->except('page'))->links("pagination::bootstrap-4") }}
		</div>
	</div>
	<div class="modal fade" id="add-product">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 id="modal-title" class="modal-title">{{ __('Add New WIP') }}</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form role="form" id="save" action="{{ route('products.wip.save') }}" method="post">
						@csrf
						<input type="hidden" id="save_id" name="id">
						<div class="form-group row">
							<label for="product_code" class="col-sm-4 col-form-label">{{ __('Product Code') }}</label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="product_code" name="product_code">
							</div>
						</div>
						<div class="form-group row">
							<label for="product_name" class="col-sm-4 col-form-label">{{ __('Product Name') }}</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="product_name" name="product_name">
							</div>
						</div>
						<div class="form-group row">
							<label for="product_amount" class="col-sm-4 col-form-label">{{ __('Amount') }}</label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="product_amount" name="product_amount" min="1">
							</div>
						</div> 
					</form>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
					<button id="button-save" type="button" class="btn btn-primary" onclick="$('#save').submit();">{{ __('Tambahkan') }}</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="delete-product">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 id="modal-title" class="modal-title">{{ __('Delete Product') }}</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form role="form" id="delete" action="{{ route('products.wip.delete') }}" method="post">
						@csrf
						@method('delete')
						<input type="hidden" id="delete_id" name="id">
					</form>
					<div>
						<p>Anda yakin ingin menghapus product code <span id="pcode" class="font-weight-bold"></span>?</p>
					</div>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
					<button id="button-save" type="button" class="btn btn-danger" onclick="$('#delete').submit();">{{ __('Ya, hapus') }}</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="wip-complete">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 id="modal-title" class="modal-title">{{ __('Selesai') }}</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form role="form" id="complete" action="{{ route('products.wip.complete') }}" method="post">
						@csrf
						<input type="hidden" id="wip_id" name="wip_id">
						<div class="form-group row">
							<label for="wip_pcode" class="col-sm-4 col-form-label">{{ __('Product Code') }}</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="wip_pcode" disabled>
							</div>
						</div>
						<div class="form-group row">
							<label for="shelf" class="col-sm-4 col-form-label">{{ __('Shelf') }}</label>
							<div class="col-sm-8">
								<select name="shelf" class="form-control" required="" id="shelf">
									{{-- <option disabled="" selected="">Pilih Shelf</option> --}}
									@foreach($shelf as $key => $val)
									<option value="{{ $key }}">{{ $val }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="wip_amount" class="col-sm-4 col-form-label">{{ __('Amount') }}</label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="wip_amount" name="amount" min="1">
							</div>
						</div>   
						<div class="form-group row">
							<label for="kilogram" class="col-sm-4 col-form-label">{{ __('Kilogram') }}</label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="kilogram" name="kilogram" min="1">
							</div>
						</div>                        
						<div class="form-group row">
							<label for="kategori" class="col-sm-4 col-form-label">Category</label>
							<div class="col-sm-8">
								<select class="form-control select2" style="width: 100%;" id="category" name="category" required>
									@foreach($category as $key => $value)
									<option value="{{ $key }}">{{ $value }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
					<button id="button-save" type="button" class="btn btn-success" onclick="$('#complete').submit();">{{ __('Stock In') }}</button>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
@section('custom-js')
<script src="/plugins/toastr/toastr.min.js"></script>
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>
	$(function () {
		var user_id;
		$('.select2').select2({
			theme: 'bootstrap4'
		});
	});

	function resetForm(){
		$('#save').trigger("reset");
	}

	function addProduct(){
		$('#modal-title').text("Add New WIP");
		$('#button-save').text("Tambahkan");
		resetForm();
	}

	function editProduct(data){
		$('#modal-title').text("Edit");
		$('#button-save').text("Simpan");
		resetForm();
		$('#save_id').val(data.pid);
		$('#product_code').val(data.pcode);
		$('#product_name').val(data.pname);
		$('#product_amount').val(data.pamount);
	}

	function wipComplete(data){
		$('#wip_id').val(data.pid);
		$('#wip_pcode').val(data.pcode);
		$('#wip_amount').val(data.pamount);
            // $('#shelf').val(data.pshelf).change().attr('readonly','readonly');
        }

        function deleteProduct(data){
        	$('#delete_id').val(data.pid);
        	$('#pcode').text(data.pcode);
        }
    </script>
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