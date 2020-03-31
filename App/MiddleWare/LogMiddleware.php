<?php
namespace App\Middleware;

use Fontibus\Database\DB;
use Fontibus\Facades\Middleware;
use Fontibus\IP\GeoIP;
use Fontibus\Route\Route;

class LogMiddleware extends Middleware {

    public static function route(Route $route) {
        if(!session()->has("logged")) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $count = DB::table('user_log')->where('ip', '=', $ip)->where('DAY(created_at)', '=', 'DAY(CURDATE())')->count('id', 'row_count')->get();
            if(!$count->has('row_count') || $count->get('row_count') == 0) {
                $GeoIP = GeoIP::get($ip);
                DB::table('user_log')->insert([
                    'ip' => $ip,
                    'country' => $GeoIP->country_code
                ]);
            }

            session()->set("logged", true);
        }

        return true;
    }

}