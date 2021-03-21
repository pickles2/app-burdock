module.exports = function(){
    let templates = {
        "main": require('./templates/main.twig'),
    };

    /**
     * プロジェクト削除モーダルダイアログを開く
     */
    this.modalDeleteProject = function( projectCode ){
        if( !projectCode ){
            alert('projectCode required.');
            return;
        }

    	let csrfToken = $('meta[name=csrf-token]').attr('content');
        let $body = $('<div>').append( templates.main({
            '_token': csrfToken,
            'name': projectCode,
        }) );
        px2style.modal({
            'title': "プロジェクトの削除",
            'body': $body,
            'form': {
                'action': "/projects/"+projectCode,
                'method': 'post',
                'submit': function(){}
            },
            'buttons': [
                $('<button>')
                    .text("削除する")
                    .addClass('px2-btn')
                    .addClass('px2-btn--danger')
                    .attr({"type":"submit"})
            ],
            'buttonsSecondary': [
                $('<button>')
                    .text("キャンセル")
                    .addClass('px2-btn')
                    .on('click', function(){
                        px2style.closeModal();
                    })
            ]
        });
    }
}
