@extends('layouts.default')
@section('title', 'デフォルト基本認証パスワードの更新')

@section('content')


<div>
<form action="javascript:;" method="post" id="cont-basicauth-default-htpasswd-form">

<p>ID: <input type="text" name="basicauth_id" value="{{ $basicauth_id }}" class="px2-input" /></p>
<p>PW: <input type="password" name="basicauth_pw" value="" class="px2-input" /> (変更する場合にのみ入力してください)</p>

<p><button class="px2-btn px2-btn--primary">更新する</button></p>
</form>
</div>


<script>
$(window).on('load', function(){
	$('#cont-basicauth-default-htpasswd-form').on('submit', function(){
		let $form = $(this);
		let basicauth_id = $form.find('input[name=basicauth_id]').val();
		let basicauth_pw = $form.find('input[name=basicauth_pw]').val();

		$.ajax({
			"url": '/system-maintenance/basicauth-default-htpasswd/ajax',
			"headers": {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			"type": 'post',
			"data": {
				"cmd": "update",
				"basicauth_id": basicauth_id,
				"basicauth_pw": basicauth_pw,
			},
			"success": function(data){
				console.log(data);
			},
			"error": function(error){
				console.error(error);
			},
			"complete": function(){
				alert('Completed.');
			}
		});
	});
});
</script>

<hr />
<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection

@section('foot')
<script>
$(window).on('load', function(){
})
</script>
@endsection
