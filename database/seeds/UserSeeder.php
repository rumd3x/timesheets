<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(1);
        if ($user) {
            return;
        }

        $user = new User([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('changethis'),
            'api_key' => Str::random(40),
            'email_verified_at' => Carbon::now(),
        ]);

        $user->save();
    }
}
