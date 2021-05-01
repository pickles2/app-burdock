@extends('layouts.default')
@section('title', 'バーチャルホスト再生成')
@section('content')

<p>Artisanコマンド <code>bd:generate_vhosts</code> を手動で実行します。</p>
<div class="px2-p">
    <p><button data-btn="generate-vhosts" class="px2-btn px2-btn--primary">bd:generate_vhosts</button></p>
</div>

<script>
window.addEventListener('load', function(){
    $('[data-btn=generate-vhosts]').on('click', function(){
        var $this = $(this);
        $this.attr('disabled', true);
        $.ajax({
            'url': '/system-maintenance/generate_vhosts/ajax_generate_vhosts',
            'method': 'post',
            'headers': {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            'contentType': 'application/json',
            'dataType': 'json',
            'data': {
                // 'cmd': param
            },
            'success': function(data){
                console.log(data);
                // $('.cont-checkcommand-'+param+' pre code').text(data.version);
            },
            'complete': function(){
                // console.log('done');
                $this.removeAttr('disabled');
            }
        });
    });

    window.Echo.channel('system-maintenance___generate_vhosts').listen('AsyncGeneralProgressEvent', (message) => {
        console.log(message);
    });
});

</script>

<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection
