<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

if (!function_exists('auth_is_admin')) {
	function auth_is_admin()
	{
		return preg_match('/^(superadmin|admin)$/i', admin_role() ?? null);
	}
}

if (!function_exists('is_superadmin')) {
	function is_superadmin()
	{
		$user_name = "Guest";

		if($user_name != 'superadmin')
		{
			Validator::make(['name' => $user_name], [
				'name' => 'required|in:superadmin'
			], ['in' => 'Action not allowed in demo version.'])->validate();
		}
	}
}

if (!function_exists('get_file_link')) {
    function get_file_link($path) {
        if (substr_count($path, 'http') > 0) return $path;
        else return asset('storage/'.$path);
    }
}

if (!function_exists('admin_role')) {
    function admin_role() {
        return Auth::guard('admin')->user()->role;
    }
}

if (!function_exists('admin_name')) {
    function admin_name() {
        return Auth::guard('admin')->user()->name;
    }
}

if (!function_exists('currency')) {
    function currency($amount) {
        return config('app.currency').' '.$amount;
    }
}

if (!function_exists('decryptStr')) {
    function decryptStr($string) {
        if(is_null($string)) return "";
        return Crypt::decryptString($string);
    }
}
