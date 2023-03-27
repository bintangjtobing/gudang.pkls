@extends('layouts.main')
@section('title', __('Stock In'))
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
		@include('dashboard_menu')
	</div>
	<div class="card">
		<div class="card-header">
			Stock In
		</div>
		<div class="card-body">
			<div class="row">
				<form class="col-12" method="POST" action="{{ action('ProductController@stockInStore') }}">
					@csrf
					<div class="form-group row">
						<label for="product_code" class="col-sm-2 col-form-label">Product Code</label>
						<div class="col-sm-10">
							<input type="number" required class="form-control" id="product_code" name="product_code" value="" >
						</div>
					</div>
					<div class="form-group row">
						<label for="product_name" class="col-sm-2 col-form-label">Product Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="product_name" value="" name="product_name" required>
						</div>
					</div>	
					<div class="form-group row" id="inputshelf">
						<label for="shelf" class="col-sm-2 col-form-label">Shelf</label>
						<div class="col-sm-10">
							<select class="form-control select2" style="width: 100%;" id="shelf" name="shelf" required>
								@foreach($shelf as $key => $value)
								<option value="{{ $key }}">{{ $value }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label for="ukuran" class="col-sm-2 col-form-label">Ukuran</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="ukuran" value="" name="ukuran">
						</div>
					</div>
					<div class="form-group row">
						<label for="kategori" class="col-sm-2 col-form-label">Category</label>
						<div class="col-sm-10">
							<select class="form-control select2" style="width: 100%;" id="category" name="category" required>
								@foreach($category as $key => $value)
								<option value="{{ $key }}">{{ $value }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label for="pamount" class="col-sm-2 col-form-label">{{ __('Amount') }}</label>
						<div class="col-sm-10">
							<input type="number" class="form-control" id="pamount" name="pamount" min="1" value="1" required>
						</div>
					</div>
					<div class="form-group row">
						<label for="kilogram" class="col-sm-2 col-form-label">Kilogram</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="kilogram" value="" name="kilogram" required>
						</div>
					</div>

					<div class="row">
						<div class="col-auto">
							<button id="button-save" type="submit" class="btn btn-primary">Submit</button>
							
						</div>
					</div>
				</form>
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
		$('#form').hide();
		loader(0);
		// $('.select2').select2({
		// 	theme: 'bootstrap4'
		// });
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	});

	$('#pcode').on('input', function() {
		$("#form").hide();
		$("#button-update").hide();
	});

	function resetForm(){
		$('#form').trigger("reset");
		$('#pcode').val('');
		$("#button-update").hide();
		$('#pcode').prop("disabled", false);
		$('#button-check').prop("disabled", false);
	}

	function stockForm(type=1){
		$("#form").hide();
		resetForm();
		$("#type").val(type);
		if(type == 1){
			$('#modal-title').text("Stock In");
			$('#button-update').text("Stock In");
		} else {
			$('#modal-title').text("Stock Out");
			$('#button-update').text("Stock Out");
			$('#inputshelf').hide();
		}
	}

	function getShelf(pid=null){
		var type = $('#type').val();
		$.ajax({
			url: '/products/shelf',
			type: "GET",
			data: {"format":"json", "product_id":pid},
			dataType: "json",
			success:function(data) {
				$('#shelf').empty();
				$('#shelf').append('<option value="">.:: Select Shelf ::.</option>');
				$.each(data, function(key, value) {
					if(type == 1){
						$('#shelf').append('<option value="'+ value.shelf_id +'">'+ value.shelf_name +'</option>');
					} else {
						$('#shelf').append('<option value="'+ value.shelf_id +'">'+ value.shelf_name +' (Stock: '+value.product_amount+')</option>');
					}
				});
			}
		});
	}

	function enableStockInput(){
		$('#button-update').prop("disabled", false);
		$("#button-update").show();
		$('#form').show();
	}

	function disableStockInput(){
		$('#button-update').prop("disabled", true);
		$("#button-update").hide();
		$('#form').hide();
	}

	function loader(status=1){
		if(status == 1){
			$('#loader').show();
		} else {
			$('#loader').hide();
		}
	}

	function productCheck(){
		var pcode = $('#product_code').val();
		if(pcode.length > 0){
			$.ajax({
				url: '/products/check/'+pcode,
				type: "GET",
				data: {"format": "json"},
				dataType: "json",
				success:function(data) {
					console.log(data);
					loader(0);
					if(data.status == 1){
						$('#product_code').empty().val(data.data.product_code).attr('readonly','readonly');
						$('#product_name').empty().val(data.data.product_name).attr('readonly','readonly');
						$('#ukuran').empty().val(data.data.ukuran);
						$('#category').val(data.data.category_id).change();
						$('#shelf').val(data.data.Shelf_id).change().attr('readonly','readonly');
						$('#pamount').empty();
						$('#kilogram').empty();
					}
				}, error:function(){
					$('#pcode').prop("disabled", false);
					$('#button-check').prop("disabled", false);
				}
			});
		} else {
			toastr.error("Product Code belum diisi!");
		}
	}

	function stockUpdate(){
		loader();
		$('#pcode').prop("disabled", true);
		$('#button-check').prop("disabled", true);
		$('#button-update').prop("disabled", true);
		disableStockInput();
		var data = {
			product_id:$('#pid').val(),
			amount:$('#pamount').val(),
			shelf:$('#shelf').val(),
			type:$('#type').val(),
		}

		$.ajax({
			url: '/products/stockUpdate',
			type: "post",
			data: JSON.stringify(data),
			dataType: "json",
			contentType: 'application/json',
			success:function(data) {
				loader(0);
				if(data.status == 1){
					toastr.success(data.message);
					resetForm();
				} else {
					toastr.error(data.message);
					enableStockInput();
					$('#pcode').prop("disabled", false);
					$('#button-check').prop("disabled", false);
				}
			}, error:function(data){
				loader(0);
				toastr.error("Unknown error! Please try again later!", data);
				resetForm();
			}
		});
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