<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('email_list', function ($attribute, $value) {
            foreach (explode(",", $value) as $em) {
                $em = trim($em);
                if (empty($em) || !filter_var($em, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                $validator = Validator::make(['email' => $em], ['email' => 'email']);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        }, 'The :attribute must be a comma separated list of valid emails.');

        Validator::extend('old_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, current($parameters));
        }, 'The :attribute field does not match.');
    }
}
