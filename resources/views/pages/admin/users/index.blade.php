@extends('adminlte::page')
@include('pages.admin.users.index.breadcrumbs')
@section('content_header')
	<div class="header">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<a class="btn btn-success float-right text-white" href="{{ route('admin.users.create') }}" title="@lang('system.create_m', ['value' => trans('system.company')])">
					<i class="fas fa-plus"></i>
				</a>
			</div>
		</div>
		<h2><strong>@lang('system.index', ['value' => trans('system.users')])</strong></h2>
	</div>
@stop
@section('content')
<div class="card project_list">
	@if ($users)
	@include('pages.admin.users.index.table')
	@else
	<div class="alert alert-dismissible alert-danger text-center" role="alert">
		<div class="container">
			<strong>@lang('system.no_results_m', ['value' => trans('system.company')])</strong>
			<button class="close" type="button" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="fas fa-times"></i></span>
			</button>
		</div>
	</div>
	@endif
</div>
@endsection
@include('pages.admin.users.form.styles')
@include('pages.admin.users.form.scripts')
