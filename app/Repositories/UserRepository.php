<?php

namespace App\Repositories;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

final class UserRepository
{
    /**
     * Undocumented function
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param bool $verified
     * @return User
     */
    public static function insert(string $name, string $email, string $password, bool $verified = false)
    {
        do {
            $apiKey = Str::random(40);
            $user = self::findByApiKey($apiKey);
        } while ($user);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'api_key' => $apiKey,
            'email_verified_at' => $verified ? Carbon::now() : NULL,
        ]);
    }

    /**
     * Find active user by ID
     *
     * @param integer $id
     * @return User|null
     */
    public static function findById(int $id)
    {
        return User::find($id);
    }

    /**
     * Returns all active Users
     *
     * @return User[]
     */
    public static function allActive()
    {
        return User::all();
    }

    /**
     * Find user by it's API key
     *
     * @param string $apiKey
     * @return User|null
     */
    public static function findByApiKey(string $apiKey)
    {
        return User::where('api_key', $apiKey)->first();
    }

    /**
     * Edit the user, overriding data on the database with data passed
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    public static function edit(User &$user, array $data)
    {
        unset($data['password']);
        $success = User::find($user->id)->update($data);

        if ($success) {
            $user = self::findById($user->id);
        }

        return $success;
    }

    /**
     * Changes the user password
     *
     * @param User $user
     * @param string $newPassword
     * @return bool
     */
    public static function changePassword(User $user, string $newPassword)
    {
       return User::find($user->id)->update(['password' => Hash::make($newPassword)]);
    }
}
