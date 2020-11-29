<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UsersEmailWhiteList implements Rule
{


	/**
	 * メールアドレスのホワイトリスト
	 *
	 * アスタリスク `*` でワイルドカードを指定できます。
	 * 許容するアドレスのパターンが複数ある場合は、配列要素に追加してください。
	 * 配列が 0件 の場合は、すべてのアドレスを許容します。
	 */
	private $email_white_list = array(
		// '*@example.com',
	);


	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
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

		$white_list = $this->email_white_list;

		if( is_array($white_list) && count($white_list) ){
			$is_hit_white_list = false;
			foreach( $white_list as $white_email_pattern ){
				$preg_pattern = preg_quote($white_email_pattern, '/');
				$preg_pattern = preg_replace('/'.preg_quote(preg_quote('*','/'),'/').'/', '.*', $preg_pattern);
				if( preg_match('/^'.$preg_pattern.'$/s', $value) ){
					$is_hit_white_list = true;
					break;
				}
			}
			if(!$is_hit_white_list){
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
