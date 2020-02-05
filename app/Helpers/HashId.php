<?php
namespace App\Helpers;

use Hashids\Hashids;
use Auth;

class HashId { 
    public static function encode(int $number) {
        $salt = env('HASHIDS_SALT', '');
        $pepper = !empty(\Auth::user()) ? substr(\Auth::user()->api_token, 0, 8) : '';
        $hashids = new Hashids($salt.$pepper);
        return $hashids->encode(rand(0, 10000), $number, rand(0, 10000));
    }

    public static function decode($hash) {
        $salt = env('HASHIDS_SALT', '');
        $pepper = !empty(\Auth::user()) ? substr(\Auth::user()->api_token, 0, 8) : '';
        $hashids = new Hashids($salt.$pepper);
        return $hashids->decode($hash)[1];
    }
}