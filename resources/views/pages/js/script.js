var url = window.location.protocol + '//' + window.location.host;
var a = document.getElementsByTagName('a');

for(var i = 0; i < a.length; i++) {
    // hrefがurlを含むかつonclickが存在する場合onclickを削除
    if(a[i].href.match(url) && a[i].onclick) {
        a[i].removeAttribute("onclick");
    };
    // クリックイベント
    a[i].addEventListener("click", function(event) {
        var href = this.href;
        var onclick = this.onclick;
        var path = href.replace(url, '');
        // 例外の終了処理
        if (!href.match(url)) {
            return;
        } else if (path.match('#')) {
            return;
        };
        // イベント無効処理
        event.preventDefault();
        // pathをエンコード処理
        path = btoa(unescape(encodeURIComponent(path)));
        // 親フレームにpathを送信
        window.parent.postMessage(path, parent_url);
    });
};
