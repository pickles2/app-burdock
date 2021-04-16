@php
	$title = __('Mypage') . ': ' . $user->name;
@endphp
@extends('layouts.default')
@section('content')

{{-- 編集・削除ボタン --}}
@can('edit', $user)
	<div class="px2-p">
		<a href="{{ url('mypage/edit') }}" class="px2-btn px2-btn--primary">プロフィールを編集する</a>
		<button type="button" class="px2-btn px2-btn--danger" data-cont-method="withdraw">退会する</button>
		<form action="{{ url('mypage/') }}" method="post" id="form-withdraw" style="display: none;">
			@csrf
			@method('DELETE')
		</form>
	</div>
	<script type="text/template" id="template-form-withdraw">
		<p>{{ __('Are you sure to delete?') }}</p>
	</script>
	<script>
	$(window).on('load', function(){
		$('[data-cont-method=withdraw]').on('click', function(){
			var $body = $('<div>' + $('#template-form-withdraw').html() + '</div>');
			px2style.modal({
				"title": "{{ __('Confirm delete') }}",
				"body": $body,
				"buttons": [
					$('<button>')
						.text('退会する')
						.addClass('px2-btn')
						.addClass('px2-btn--danger')
						.on('click', function(){
							px2style.loading();
							$('#form-withdraw').submit();
						})
				],
				"buttonsSecondary": [
					$('<button>')
						.text('キャンセル')
						.addClass('px2-btn')
						.on('click', function(){
							px2style.closeModal();
						})
				]
			});
		});
	});
	</script>
@endcan

{{-- ユーザー1件の情報 --}}
<div class="px2-p">
	<table class="px2-table">
		<tr>
			<th>{{ __('ID') }}</th>
			<td>{{ $user->id }}</td>
		</tr>
		<tr>
			<th>{{ __('Name') }}</th>
			<td>{{ $user->name }}</td>
		</tr>
		<tr>
			<th>{{ __('E-Mail Address') }}</th>
			<td>{{ $user->email }} <a href="{{ url('mypage/edit_email') }}" class="px2-btn px2-btn--primary">変更する</a></td>
		</tr>
	</table>
</div>

{{-- ユーザーの記事一覧 --}}
<h2>{{ __('Projects') }}</h2>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ __('Project Name') }}</th>
				<th>{{ __('Git URL') }}</th>
				<th>{{ __('Created') }}</th>
				<th>{{ __('Updated') }}</th>

				{{-- 記事の編集・削除ボタンのカラム --}}
				@can('edit', $user)
					<th></th>
				@endcan
			</tr>
		</thead>
		<tbody>
			@foreach ($user->projects as $project)
@php
$gitUtil = new \App\Helpers\git($project);
@endphp
				<tr>
					<td>
						<a href="{{ url('home/'.urlencode($project->project_code)) }}">
							{{ $project->project_name }}
						</a>
					</td>
					<td>{{ $project->git_url }}</td>
					<td>{{ $project->created_at }}</td>
					<td>{{ $project->updated_at }}</td>
					@can('edit', $user)
						<td nowrap>
							<a href="{{ url('projects/'.urlencode($project->project_code).'/edit') }}" class="btn btn-primary">
								{{ __('Edit') }}
							</a>
							@component('components.btn-del')
								@slot('controller', 'projects')
								@slot('id', $project->id)
								@slot('code', $project->project_code)
								@slot('name', $project->project_name)
								@slot('branch', $gitUtil->get_remote_default_branch_name())
							@endcomponent
						</td>
					@endcan
					</tr>
			@endforeach
		</tbody>
	</table>
</div>
{{ $user->projects->links() }}

@endsection
