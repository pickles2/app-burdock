<?php
if (! function_exists('locale_url')) {
    /**
     * 言語切り替え用のURLを生成する
     *
     * @param string $locale 使用言語
     * @return string 言語切り替え用のURL
     */
    function locale_url($locale)
    {
        // URLをパースする
        $urlParsed = parse_url(Request::fullUrl());
        if (isset($urlParsed['query'])) {
            parse_str($urlParsed['query'], $params);
        }
        // GETパラメータのlangnの値に引数を格納する
        $params['lang'] = $locale;
        // クエリ部分を整形する
        $paramsJoined = [];
        foreach($params as $param => $value) {
           $paramsJoined[] = "$param=$value";
        }
        $query = implode('&', $paramsJoined);
        // URL全体を整形する
        $url = (App::environment('production') ? 'https' : $urlParsed['scheme']).'://'.
               $urlParsed['host']. // user と pass は扱わない
               (isset($urlParsed['port']) ? ':'.$urlParsed['port'] : '').
               (isset($urlParsed['path']) ? $urlParsed['path'] : '/').
               '?'.$query.
               (isset($urlParsed['fragment']) ? '#'.$urlParsed['fragment'] : '');
        return $url;
    }
}
