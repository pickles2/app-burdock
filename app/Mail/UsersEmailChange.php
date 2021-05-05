<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UsersEmailChange extends Mailable
{
	use Queueable, SerializesModels;

	protected $usersEmailChange;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($usersEmailChange)
	{
		$this->usersEmailChange = $usersEmailChange;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$url = url('mypage/edit_email_update').'?token='.urlencode($this->usersEmailChange->token);

		return $this
			// ->from('hoge@hoge.com') // 送信元
			->subject('メールアドレス変更') // メールタイトル
			->view('emails.email_change') // どのテンプレートを呼び出すか
			->with([ // withオプションでセットしたデータをテンプレートへ受け渡す
				'usersEmailChange' => $this->usersEmailChange,
				'linkto' => $url,
			])
		;
	}
}
