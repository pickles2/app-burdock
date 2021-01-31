<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * 参照: https://readouble.com/laravel/5.7/ja/requests.html#configuring-trusted-proxies
     *
     * Amazon AWSや他の「クラウド」ロードバランサプロバイダを使用している場合は、
     * 実際のバランサのIPアドレスは分かりません。
     * このような場合、全プロキシを信用するために、 `*` を使います。
     *
     * NOTE: Amazon AWSや他の「クラウド」ロードバランサプロバイダを使用している場合。
     * 実際のバランサのIPアドレスが分らないので、ワイルドカード `*` で表現する。
     *
     * ```
     * protected $proxies = '*';
     * ```
     *
     * NOTE: ワイルドカード `*` を2つ書くと、最も近いプロキシだけでなくすべてのプロキシを信頼するらしい。
     *
     * ```
     * protected $proxies = '**';
     * ```
     *
     * @var array
     */
    protected $proxies;


    /**
     * The headers that should be used to detect proxies.
     *
     * 参照: https://readouble.com/laravel/5.7/ja/requests.html#configuring-trusted-proxies
     *
     * AWS Elastic Load Balancingを使用している場合、
     * `$headers` の値は `Request::HEADER_X_FORWARDED_AWS_ELB` に設定する必要があります。
     *
     * ```
     * protected $headers = Request::HEADER_X_FORWARDED_AWS_ELB;
     * ```
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;


}
