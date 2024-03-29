<?php
namespace Fontibus\Facades;

use App\User;
use Exception;
use Fontibus\Cookie\Cookie;
use Fontibus\Query\DB;
use Fontibus\IP\GeoIP;
use Fontibus\Route\Redirect;
use Fontibus\String\Str;

class Auth {

    private static bool $checked = false;
    private static bool $guest = true;
    private static User $user;

    private static array $keys = [
        'user_id', 'log_date', 'key'
    ];

    /**
     * Return if user is guest
     * @return bool
     */
    public static function isGuest(): bool {
        return self::$guest;
    }

    /**
     * Check if user is authenticated
     * @throws Exception
     */
    public static function check(): void {
        if(self::$checked)
            return;

        $number = rand(1, 10);
        if($number == 10) {
            $day = date("Y-m-d H:i:s", strtotime('-3 day'));
            $year = date("Y-m-d H:i:s", strtotime('-1 year'));
            DB::table('sessions')->where('log_date', '<=', $year)->orWhere('last_active', '<=', $day)->delete();
        }

        if(!Cookie::hasCookie('login-session')) {
            self::$checked = true;
            return;
        }

        try {
            $session = Cookie::getCookie('login-session');
        } catch(Exception $e) {
            self::$checked = true;
            return;
        }

        if(empty($session)) {
            self::$checked = true;
            return;
        }

       foreach(self::$keys as $key) {
           if(!array_key_exists($key, $session)) {
               self::$checked = true;
               Cookie::clearCookie('login-session');
               return;
           }
       }

       $user = User::find($session['user_id']);
       if(empty($user)) {
           self::$checked = true;
           Cookie::clearCookie('login-session');
           return;
       }

       $result = DB::table('sessions')->count('id', 'count')->where([
           'session_key' => $session['key'],
           'user_id' => $session['user_id']
       ])->first();
       if(empty($result) || $result->count < 1) {
           self::$checked = true;
           Cookie::clearCookie('login-session');
           return;
       }

       if(strtotime($session['log_date']) <= date("Y-m-d H:i:s", strtotime('-1 year'))) {
           self::$checked = true;
           DB::table('sessions')->where([
               'session_key' => $session['key'],
               'user_id' => $session['user_id']
           ])->delete();
           Cookie::clearCookie('login-session');
           Redirect::route('login');
           return;
       }

       self::$guest = false;
       self::$checked = true;
       self::$user = $user;
    }

    /**
     * Get id of user
     * @return int
     */
    public static function getId(): int {
        if(empty(self::$user))
            return 0;

        return self::$user->id;
    }

    /**
     * Get User Model
     * @return User|null
     */
    public static function user() {
        if(self::$guest)
            return null;

        return self::$user;
    }

    /**
     * Try to log in with email and password
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function login(string $email, string $password): bool {
        if(!self::$guest)
            return true;

        if(empty($email) || empty($password))
            return false;

        $user = User::where('email', '=', $email)->select(['id', 'password'])->first();
        if(empty($user))
            return false;

        if(!password_verify($password,$user->password))
            return false;

        $key = Str::random(16);
        $data = [
            'user_id' => $user->id,
            'log_date' => date("Y-m-d H:i:s"),
            'key' => $key
        ];

        $result = DB::table('sessions')->insert([
            'user_id' => $user->id,
            'session_key' => $key
        ]);

        $ip = $_SERVER['REMOTE_ADDR'];
        $GeoIP = GeoIP::get($ip);
        DB::table('login_log')->insert([
            'user_id' => $user->id,
            'ip' => $ip,
            'country' => $GeoIP->country_code
        ]);

        if($result < 1)
            return false;

        Cookie::setCookie('login-session', $data, 60 * 60 * 24 * 365);
        return true;
    }

    /**
     * Logout
     * @throws Exception
     */
    public static function logout() {
        if(!Cookie::hasCookie('login-session'))
            Redirect::route('home');

        try {
            $session = Cookie::getCookie('login-session');
        } catch(Exception $e) {
            Redirect::back();
        }

        if(empty($session)) {
            self::$checked = true;
            return;
        }

        if(isset($session['key']) && isset($session['user_id'])) {
            DB::table('sessions')->where([
                'session_key' => $session['key'],
                'user_id' => $session['user_id']
            ])->delete();
        }

        Cookie::clearCookie('login-session');
        self::$guest = true;
        Redirect::route('home');
    }

}