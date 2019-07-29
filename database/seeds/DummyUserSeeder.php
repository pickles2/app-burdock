<?php

use Illuminate\Database\Seeder;
use App\User;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fakerを使う
        $faker = Faker\Factory::create('ja_JP');

        // 固定ユーザーを作成
        $user = new User;
        $user->name = 'test0001';
        $user->email = 'test0001@example.com';
        $user->password = bcrypt('test0001');
        $user->lang = 'ja';
        $user->email_verified_at = $faker->dateTime();
        $user->created_at = $faker->dateTime();
        $user->updated_at = $faker->dateTime();
        $user->save();

        $user = new User;
        $user->name = 'test0002';
        $user->email = 'test0002@example.com';
        $user->password = bcrypt('test0002');
        $user->lang = 'en';
        $user->email_verified_at = $faker->dateTime();
        $user->created_at = $faker->dateTime();
        $user->updated_at = $faker->dateTime();
        $user->save();

        // ランダムにユーザーを作成
        for ($i = 0; $i < 18; $i++)
        {
            $user = new User;
            $user->name = $faker->unique()->userName();
            $user->email = $faker->unique()->email();
            $user->password = bcrypt('test0003');
            $user->lang = $faker->randomElement(['en', 'ja']);
            $user->email_verified_at = $faker->dateTime();
            $user->created_at = $faker->dateTime();
            $user->updated_at = $faker->dateTime();
            $user->save();
        }
    }
}
