<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UsersEmailAllowList;
use App\User;
use Illuminate\Validation\Rule;

class StoreUser extends FormRequest
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
    public function rules(User $user = null, $new_email = null)
    {
		if ( !is_null($user) ) {
            // 編集画面の時
			// $unique = 'unique:users,email,'.$id.',id';
			$unique = Rule::unique('users')
				->ignore($user->id)
				->where(function($query) use ($user, $new_email) {
					$query
						// ->withTrashed()
						->where('email', $new_email)
						->where('id', '<>', $user->id)
						->whereNull('deleted_at')
					;
				});
		} else {
            // 新規登録画面の時
			$unique = 'unique:users,email';
		}

        return [
            'name' => 'required|string|max:191',
			'email' => [
				'required',
                $unique,
				'string',
				'email',
				'max:255',
                new UsersEmailAllowList,
			],
            // 'email' => 'unique:users',
            'password' => 'required|string|min:6|max:191|confirmed',
        ];
    }
}
