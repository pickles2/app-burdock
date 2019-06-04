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
        // 管理ユーザーを作成
        $date = date('Y-m-d H:i:s');
        $user = new User;
        $user->name = 'admin';
        $user->email = 'admin@localhost';
        $user->password = bcrypt('admin');
        $user->lang = 'ja';
        $user->email_verified_at = $date;
        $user->created_at = $date;
        $user->updated_at = $date;
        $user->save();
    }
}
