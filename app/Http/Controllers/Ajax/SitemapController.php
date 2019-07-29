<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSitemap;
use App\Http\Controllers\Controller;
use App\Project;

class SitemapController extends Controller
{
    //
    /**
     * 各アクションの前に実行させるミドルウェア
     */
    public function __construct()
    {
        // ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
        $this->middleware('auth');
        $this->middleware('verified');
    }

    public function uploadAjax(Request $request)
    {
        //
        $status = 0;
        if(isset($request->str)) {
            if(!($request->str === 'text/csv' || $request->str === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                $error = 'ファイルがcsvまたはxlsxではありません。';
            } else {
                $error = '';
                $status = 1;
            }
        }

        $data = array(
            "error" => $error,
            "status" => $status,
        );
        return $data;
    }
}
