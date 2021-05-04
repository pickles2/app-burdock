@extends('layouts.default')
@section('title', 'イベントログ')
@section('content')

{{-- イベントログ一覧 --}}
<h2>Event Logs</h2>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ __('Project Name') }}</th>
				<th>{{ __('Message') }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($eventLogs as $eventLog)
				<tr>
					<td>
						<a href="{{ url('home/'.urlencode($eventLog->project_code)) }}">
							{{ $eventLog->project_code }}
						</a>
					</td>
					<td>{{ $eventLog->message }}</td>
                </tr>
			@endforeach
		</tbody>
	</table>
</div>
{{ $eventLogs->links() }}



<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection
