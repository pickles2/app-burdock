@if (isset($bootstrap) && $bootstrap == 3)
<!-- jQuery -->
<script src="/common/scripts/jquery-2.2.4.min.js" type="text/javascript"></script>

<!-- Bootstrap3 -->
<link rel="stylesheet" href="/common/bootstrap/css/bootstrap.css">
<script src="/common/bootstrap/js/bootstrap.min.js"></script>
@else
<!-- jQuery -->
<script src="/common/scripts/jquery-3.5.1.min.js" type="text/javascript"></script>

<!-- Bootstrap4 -->
<link rel="stylesheet" href="/common/bootstrap4/css/bootstrap.css">
<script src="/common/bootstrap4/js/bootstrap.min.js"></script>
@endif

<!-- Pickles 2 Style -->
{{-- <!-- 
NOTE: CSS `px2style.css` は、 `app.css` 内にビルドされるので、ここでは読み込まない。
--> --}}

<!-- App Resources -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" />

@if (property_exists($global, 'appearance') && $global->appearance)
<style>
:root {--px2-main-color: {{ $global->appearance->main_color }};}
</style>
@endif
