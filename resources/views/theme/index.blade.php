@php
	$title = __('Theme');
@endphp
@extends('layouts.default')

@section('content')

<div class="cont-main">
</div>

@endsection

@section('stylesheet')
<link rel="stylesheet" href="{{ asset('/cont/theme/style.css') }}" type="text/css" />
@endsection

@section('script')
<script>
var project_code = <?php echo json_encode($project->project_code); ?>;
var branch_name = <?php echo json_encode($branch_name); ?>;
</script>
<script src="{{ asset('/cont/theme/script.js') }}"></script>
@endsection
