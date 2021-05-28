<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Project;
use Illuminate\Validation\Rule;

class StoreProject extends FormRequest
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
	public function rules(Project $project)
	{

		if ( $this->id ) { // 編集画面の時
			// $unique = 'unique:projects,project_code,'.$this->id.',id';
			$unique = Rule::unique('projects')
				->ignore($this->input('id'))
				->where(function($query) {
					$query
						// ->withTrashed()
						->where('project_code', $this->input('project_code'))
						->where('id', '<>', $this->input('id'))
						->whereNull('deleted_at')
					;
				});
		} else { // 新規登録画面の時
			$unique = 'unique:projects,project_code';
		}

		$alpha_dash_custom = function($attribute, $value, $fail) {
			// 入力の取得
			$input_data = $this->all();

			// 条件に合致したらエラーにする
			$error_message = '';
			if(!preg_match('/^[a-z]/', $value)) {
				$error_message .= '先頭は半角英字で始まるようにしてください。';
			}
			if(!preg_match('/^[a-z\d\_\-]+$/', $value)) {
				$error_message .= '使用できない文字が含まれています。';
			}
			if(preg_match('/[\-\_]{3,}/', $value)) {
				$error_message .= '連続する3つ以上のハイフンまたはアンダースコアを含めることはできません。';
			}
			if( strlen($error_message) ){
				$fail( $error_message );
			}
		};

		return [
			'project_name' => ['required'],
			'project_code' => [
				$unique,
				$alpha_dash_custom,
				'required'
			],
			'git_url' => ['max:400'],
			'git_username' => [''],
			'git_password' => [''],
		];
	}
}
