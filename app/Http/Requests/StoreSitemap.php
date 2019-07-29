<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSitemap extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認可は別の箇所で行うので、ここでは素通りさせる
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /**
        * 検証用の関数
        *   $attribute: 検証中の属性名
        *   $value    : 検証中の属性の値
        *   $fail     : 失敗時に呼び出すメソッド?
        **/
        $file = function($attribute, $value, $fail) {
            // 入力の取得
            $input_data = $this->all();
            $mimetype = $value->clientExtension();
            // 条件に合致したらエラーにする
            if($input_data && isset($input_data['file'])) {
                if(!($mimetype === 'csv' || $mimetype === 'xlsx')) {
                    $fail('ファイルがcsvまたはxlsxではありません。');
                }
            }
        };

        return [
            'file' => [$file, 'required'],
        ];
    }
}
