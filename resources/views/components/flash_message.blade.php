{{-- フラッシュメッセージ --}}
<div id="session-my-status" class="container mt-2" style="z-index: 1000000; position: fixed; width: 100%; text-align: center; opacity: 0.9;">
	<div class="alert alert-success" style="display: inline-block; width: 80%;">
		{{ session('bd_flash_message') }}
	</div>
</div>
<script>
$(function(){
	setTimeout(function() {
		$('#session-my-status').fadeOut(500);
	}, 2000);
})
</script>
