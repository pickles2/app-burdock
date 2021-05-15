<?php

use Illuminate\Database\Seeder;
use App\User;

class MasterDataSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// NOTE: ↓ 管理ユーザー ということで作成するように仮にしていたが、
		//         いまのところ、特に特権が与えられているわけでもなく、使いみちもないので、削除した。 (2021-05-15)

		// // 管理ユーザーを作成
		// $date = date('Y-m-d H:i:s');
		// $user = new User;
		// $user->name = 'admin';
		// $user->email = 'admin@localhost';
		// $user->password = bcrypt('admin');
		// $user->lang = 'ja';
		// $user->email_verified_at = $date;
		// $user->created_at = $date;
		// $user->updated_at = $date;
		// $user->save();
	}
}
