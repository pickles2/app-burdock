@php
	$title = __('Member List');
@endphp
@extends('layouts.default')

@section('content')

<div>
	<p>登録されているユーザーの一覧を表示します。</p>
</div>

<h2>登録ユーザー</h2>

@if( count($activeUsers) )
<div class="px2-p">
<table class="px2-table" style="width:100%;">
	<thead>
	<tr>
		<th>ユーザー名</th>
		<th>メールアドレス</th>
		<th>登録日</th>
	</tr>
	</thead>
	<tbody>
@foreach($activeUsers as $user)
	<tr>
		<td>{{ $user->name }}</td>
		<td>{{ $user->email }}</td>
		<td>{{ $datetime_to_display($user->created_at) }}</td>
	</tr>
@endforeach
	</tbody>
</table>
</div>

@else
<p>登録ユーザーはいません。</p>
@endif

<h2>退会済みユーザー</h2>
<p>
	次にリストされたユーザーは、退会手続きを行っており、現在は無効です。<br />
	これらのデータは、退会してからおよそ <strong>{{ $softdelete_retention_period }}</strong> 後に、完全に消去されます。<br />
</p>

@if( count($softDeletedUsers) )
<div class="px2-p">
<table class="px2-table" style="width:100%;">
	<thead>
	<tr>
		<th>ユーザー名</th>
		<th>メールアドレス</th>
		<th>登録日</th>
		<th>退会日</th>
	</tr>
	</thead>
	<tbody>
@foreach($softDeletedUsers as $user)
	<tr>
		<td>{{ $user->name }}</td>
		<td>{{ $user->email }}</td>
		<td>{{ $datetime_to_display($user->created_at) }}</td>
		<td>{{ $datetime_to_display($user->deleted_at) }}</td>
	</tr>
@endforeach
	</tbody>
</table>
</div>

@else
<p>退会済みのユーザーはいません。</p>
@endif


@endsection
