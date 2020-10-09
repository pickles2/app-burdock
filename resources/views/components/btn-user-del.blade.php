@php
    $id_attr = 'modal-delete-' . $controller . '-' . $id;
@endphp

{{-- 削除ボタン --}}
<button type="button" class="px2-btn px2-btn--danger" data-toggle="modal" data-target="#{{ $id_attr }}">退会する</button>

{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id_attr }}-label">
                    {{ __('Confirm delete') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure to delete?') }}</p>
                <p><strong>{{ $name }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="px2-btn px2-btn--secondary" data-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                {{-- 削除用のアクションを実行させるフォーム --}}
                <form action="{{ url($controller . '/') }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px2-btn px2-btn--danger">退会する</button>
                </form>
            </div>
        </div>
    </div>
</div>
