@php
	$title = __('Custom Console Extensions');
@endphp
@extends('layouts.default')

@section('content')

<div class="cont-main">
</div>

@endsection

@section('head')
<link rel="stylesheet" href="{{ asset('/cont/custom_console_extensions/style.css') }}" type="text/css" />
@endsection

@section('foot')
<script>
var cce_id = <?php echo json_encode($cce_id); ?>;
var project_code = <?php echo json_encode($project->project_code); ?>;
var branch_name = <?php echo json_encode($branch_name); ?>;
</script>
<script src="{{ asset('/cont/custom_console_extensions/script.js') }}"></script>
@endsection
