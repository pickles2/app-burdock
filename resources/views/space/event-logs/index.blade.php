@extends('layouts.default')
@section('title', 'イベントログ')
@section('content')

{{-- イベントログ一覧 --}}
<h2>Event Logs</h2>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ __('Date') }}</th>
				<th>{{ __('User ID') }}</th>
				<th>{{ __('Project ID') }}</th>
				<th>{{ __('Function Name') }}</th>
				<th>{{ __('Event Name') }}</th>
				<th>{{ __('Progress') }}</th>
				<th>{{ __('Message') }}</th>
				<th>{{ __('Proccess ID') }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($eventLogs as $eventLog)
				<tr>
					<td>{{ $eventLog->created_at }}</td>
					<td>{{ $eventLog->user_id }}</td>
					<td>{{ $eventLog->project_id }}</td>
					<td>{{ $eventLog->function_name }}</td>
					<td>{{ $eventLog->event_name }}</td>
					<td>{{ $eventLog->progress }}</td>
					<td>{{ $eventLog->message }}</td>
					<td>{{ $eventLog->pid }}</td>
                </tr>
			@endforeach
		</tbody>
	</table>
</div>
{{ $eventLogs->links() }}



@endsection
