<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="/css/adminlte.min.css">
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
	<style type="text/css">
		.layout-fixed .wrapper .sidebar {
    height: calc(90vh - (3.5rem + 1px));
}
	</style>
	@hasSection('custom-css')
	@yield('custom-css')
	@endif
</head>
<body class="hold-transition sidebar-mini layout-fixed">
	<div class="wrapper">
		<nav class="main-header navbar navbar-expand navbar-white navbar-light">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<li class="nav-item d-none d-sm-inline-block">
					<span class="nav-link">@yield('title')</span>
				</li>
			</ul>
			@if(!empty($warehouse))
			<ul class="navbar-nav ml-auto">
				<li class="nav-item dropdown">
					<a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
						@if(Session::has('selected_warehouse_name'))
						<i class="fas fa-warehouse"></i>
						<span>{{ Session::get('selected_warehouse_name') }}</span>
						@endif
					</a>
					<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
						<span class="dropdown-item dropdown-header">Warehouse</span>
						@foreach($warehouse as $w)
						<a href="{{ route('warehouse') }}/change/{{ $w->warehouse_id }}" class="dropdown-item">
							{{ $w->warehouse_name }}
						</a>
						@endforeach
					</div>
				</li>
			</ul>
			@endif
		</nav>
		<aside class="main-sidebar sidebar-dark-primary elevation-4">
			<a href="/" class="brand-link" >
				<span class="logo-lg"><img src="{{ asset('img/logo.jpeg') }}" class="brandlogo-image" style="width: 100%"><strong style="display: none">WAREHOUSE</strong></span>
			</a>

			<div class="sidebar">
				<nav class="mt-2">
					<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
						@if(Auth::check())
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'home')? 'active':''}}" href="{{ route('home') }}">
								<i class="nav-icon fas fa-home"></i>
								<p class="text">{{ __('Dashboard') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products.wip')? 'active':''}}" href="{{ route('products.wip') }}">
								<i class="nav-icon fas fa-spinner"></i>
								<p class="text">{{ __('Work In Progress (WIP)') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products.wip.history')? 'active':''}}" href="{{ route('products.wip.history') }}">
								<i class="nav-icon fas fa-history"></i>
								<p class="text">{{ __('WIP History') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products.denah')? 'active':''}}" href="{{ route('products.denah') }}">
								<i class="nav-icon fas fa-th-large"></i>
								<p class="text">{{ __('Denah') }}</p>
							</a>
						</li>
						<li class="nav-header">Product</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products')? 'active':''}}" href="{{ route('products') }}">
								<i class="nav-icon fas fa-boxes"></i>
								<p class="text">{{ __('Products List') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products.categories')? 'active':''}}" href="{{ route('products.categories') }}">
								<i class="nav-icon fas fa-project-diagram"></i>
								<p class="text">{{ __('Categories') }}</p>
							</a>
						</li>
						@if(Auth::user()->role == 0)
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'products.shelf')? 'active':''}}" href="{{ route('products.shelf') }}">
								<i class="nav-icon fas fa-cubes"></i>
								<p class="text">{{ __('Shelf') }}</p>
							</a>
						</li>
						@endif
						<li class="nav-header">Settings</li>
						@if(Auth::user()->role == 0)
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'warehouse')? 'active':''}}" href="{{ route('warehouse') }}">
								<i class="nav-icon fas fa-warehouse"></i>
								<p class="text">{{ __('Warehouse') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'users')? 'active':''}}" href="{{ route('users') }}">
								<i class="nav-icon fas fa-users"></i>
								<p class="text">{{ __('Users') }}</p>
							</a>
						</li>
						@endif
						<li class="nav-item">
							<a class="nav-link {{ (Route::current()->getName() == 'myaccount')? 'active':''}}" href="{{ route('myaccount') }}">
								<i class="nav-icon fas fa-user-cog"></i>
								<p class="text">{{ __('My Account') }}</p>
							</a>
						</li>
						<li class="nav-item">
							<form id="logout" action="{{ route('logout') }}" method="post">@csrf</form>
							<a class="nav-link" href="javascript:;" onclick="document.getElementById('logout').submit();">
								<i class="nav-icon fas fa-sign-out-alt text-danger"></i>
								<p class="text">{{ __('Logout') }} ({{ Auth::user()->username }})</p>
							</a>
						</li>
						@else
						<li class="nav-item">
							<a class="nav-link" href="{{ route('login') }}">
								<i class="nav-icon fas fa-sign-out-alt text-danger"></i>
								<p class="text">{{ __('Login') }}</p>
							</a>
						</li>
						@endif
					</ul>
				</nav>
			</div>
		</aside>

		<div class="content-wrapper">
			@yield('content')
		</div>

		<footer class="main-footer">
			<b>Version</b> {{ config('app.version') }}
		</footer>

		<aside class="control-sidebar control-sidebar-dark">
		</aside>
	</div>

	<script src="/plugins/jquery/jquery.min.js"></script>
	<script src="/plugins/jquery-ui/jquery-ui.min.js"></script>
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="/js/adminlte.js"></script>
	@hasSection('custom-js')
	@yield('custom-js')
	@endif
</body>
</html>
