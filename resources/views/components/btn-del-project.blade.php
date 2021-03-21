@php
    $id_attr = 'modal-delete-' . $controller . '-' . $id;
@endphp

{{-- 削除ボタン --}}
<button class="px2-btn px2-btn--danger" id="btn-{{ $id_attr }}" style="margin-bottom: 16px;">このプロジェクトを削除</button>

<script>
$(window).on('load', function(){
    $('#btn-{{ $id_attr }}').on('click', function(){
        var $body = $('<div>').append( $('#template-{{ $id_attr }}').html() );
        px2style.modal({
            'title': "{{ __('Confirm delete') }}",
            'body': $body,
            'form': {
                'action': "{{ url($controller.'/'.$code) }}",
                'method': 'post',
                'submit': function(){}
            },
            'buttons': [
                $('<button>')
                    .text("{{ __('Delete') }}")
                    .addClass('px2-btn')
                    .addClass('px2-btn--danger')
                    .attr({"type":"submit"})
            ],
            'buttonsSecondary': [
                $('<button>')
                    .text("{{ __('Cancel') }}")
                    .addClass('px2-btn')
                    .on('click', function(){
                        px2style.closeModal();
                    })
            ]
        });
    });
});
</script>


{{-- モーダルウィンドウ --}}
<script id="template-{{ $id_attr }}" type="text/template">
    <p>{{ __('Are you sure to delete?') }}</p>
    <p><strong>{{ $name }}</strong></p>
</script>
