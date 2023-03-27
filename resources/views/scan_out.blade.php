@extends('layouts.main')
@section('title', __('Stock Out'))
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
			Stock Out
		</div>
		<div class="card-body">
			<div class="row">
				<form class="col-12" method="POST" action="{{ action('ProductController@stockOutStore') }}">
					@csrf
					<div class="row mb-2">
						<div class="col-12">
							<div class="input-group input-group-lg">
								<input type="number" class="form-control" id="pcode" name="pcode" min="0" placeholder="Product Code">
								<div class="input-group-append">
									<button class="btn btn-primary" id="button-check" onclick="productCheck()">
										<i class="fas fa-search"></i>
									</button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label for="product_name" class="col-sm-2 col-form-label">Product Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="product_name" value="" name="product_name" disabled>
						</div>
					</div>	
					<div class="form-group row">
						<label for="kilogram" class="col-sm-2 col-form-label">Kilogram</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="kilogram" value="" name="kilogram" disabled>
						</div>
					</div>
					<div class="form-group row">
						<label for="ukuran" class="col-sm-2 col-form-label">Ukuran</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="ukuran" value="" name="ukuran" disabled>
						</div>
					</div>
					<div class="form-group row">
						<label for="pamount" class="col-sm-2 col-form-label">{{ __('Amount') }}</label>
						<div class="col-sm-10">
							<input type="number" class="form-control" id="pamount" name="pamount" min="1" value="1" disabled>
						</div>
					</div>
					<div class="form-group row">
						<label for="pembeli" class="col-sm-2 col-form-label">{{ __('Pembeli') }}</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="pembeli" name="pembeli" disabled>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<input type="hidden" id="product_id" name="product_id"> 
							<button id="button-save" type="submit" class="btn btn-primary col-12" disabled="">Submit</button>
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
		$('.select2').select2({
			theme: 'bootstrap4'
		});
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
		var pcode = $('#pcode').val();
		if(pcode.length > 0){
			loader();
			$('#form').hide();
			$('#pcode').prop("disabled", true);
			$('#button-check').prop("disabled", true);
			$.ajax({
				url: '/products/check/'+pcode,
				type: "GET",
				data: {"format": "json"},
				dataType: "json",
				success:function(data) {
					loader(0);
					if(data.status == 1){
						$('#product_id').empty().val(data.data.product_id);
						$('#product_code').empty().val(data.data.product_code);
						$('#product_name').empty().val(data.data.product_name);
						$('#kilogram').empty().val(data.data.kilogram);
						$('#ukuran').empty().val(data.data.ukuran).removeAttr('disabled');
						$('#pamount').empty().removeAttr('disabled');
						$('#pembeli').empty().removeAttr('disabled');
						$('#button-save').removeAttr('disabled');

					} else {

						toastr.error("Product Code tidak dikenal!");
					}
					$('#pcode').prop("disabled", false);
					$('#button-check').prop("disabled", false);
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