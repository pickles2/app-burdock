<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UsersEmailAllowList;

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
    public function rules($id = null)
    {
        return [
            'name' => 'required|string|max:191',
			'email' => [
				'required',
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
