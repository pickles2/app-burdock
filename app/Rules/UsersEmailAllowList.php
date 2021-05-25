<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UsersEmailAllowList implements Rule
{


	/**
	 * メールアドレスの許可リスト
	 *
	 * アスタリスク `*` でワイルドカードを指定できます。
	 * 許容するアドレスのパターンが複数ある場合は、配列要素に追加してください。
	 * 配列が 0件 の場合は、すべてのアドレスを許容します。
	 */
	private $user_email_allowlist = array(
		// '*@example.com',
	);


	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$user_email_allowlist = config('burdock.user_email_allowlist');
		$user_email_allowlist_ary = explode(',', $user_email_allowlist);
		foreach( $user_email_allowlist_ary as $email_pattern ){
			$email_pattern = trim($email_pattern);
			if( !strlen($email_pattern) ){
				continue;
			}
			array_push($this->user_email_allowlist, $email_pattern);
		}
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{

		$allow_list = $this->user_email_allowlist;

		if( is_array($allow_list) && count($allow_list) ){
			$is_hit_allow_list = false;
			foreach( $allow_list as $allow_email_pattern ){
				$preg_pattern = preg_quote($allow_email_pattern, '/');
				$preg_pattern = preg_replace('/'.preg_quote(preg_quote('*','/'),'/').'/', '.*', $preg_pattern);
				if( preg_match('/^'.$preg_pattern.'$/s', $value) ){
					$is_hit_allow_list = true;
					break;
				}
			}
			if(!$is_hit_allow_list){
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'Invalid E-mail address.';
	}
}
