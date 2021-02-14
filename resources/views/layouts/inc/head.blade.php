@if (isset($bootstrap) && $bootstrap == 4)
<!-- jQuery -->
<script src="/common/scripts/jquery-3.5.1.min.js" type="text/javascript"></script>

<!-- Bootstrap4 -->
<link rel="stylesheet" href="/common/bootstrap4/css/bootstrap.css">
<script src="/common/bootstrap4/js/bootstrap.min.js"></script>
@else
<!-- jQuery -->
<script src="/common/scripts/jquery-2.2.4.min.js" type="text/javascript"></script>

<!-- Bootstrap -->
<link rel="stylesheet" href="/common/bootstrap/css/bootstrap.css">
<script src="/common/bootstrap/js/bootstrap.min.js"></script>
@endif

<!-- Pickles 2 Style -->
<link rel="stylesheet" href="/common/px2style/dist/px2style.css" charset="utf-8">
<script src="/common/px2style/dist/px2style.js" charset="utf-8"></script>

<!-- App Resources -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" />
