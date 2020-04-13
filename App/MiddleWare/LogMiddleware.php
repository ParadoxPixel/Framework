<?php
namespace App\Middleware;

use Fontibus\Facades\Middleware;
use Fontibus\IP\GeoIP;
use Fontibus\Query\DB;
use Fontibus\Route\Route;
use Fontibus\Route\Router;

class LogMiddleware extends Middleware {

    public static function route(Route $route) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $route = Router::getRoute();
        $path = !empty($route) ? $route->getPath() : '';
        if(!session()->has("request_log")) {
            $count = DB::table('user_log')
                ->where('ip', '=', $ip)
                ->where(DB::raw('`created_at`=CURDATE()'))
                ->count('id', 'row_count')
                ->first();

            if(!$count->has('row_count') || $count->get('row_count') == 0) {
                self::newSession($ip);
            } else {
               self::newRequest($ip);
            }

            session()->set('request_log', json_encode([
                'date' => date('d-m-Y'),
                'request' => date('H:i:s')
            ]));
            session()->set("previous_path", $path);
        } else {
            $array = json_decode(session()->get('request_log'));
            if($array->date != date('d-m-Y')) {
                self::newSession($ip);
                session()->set('request_log', json_encode([
                    'date' => date('d-m-Y'),
                    'request' => date('H:i:s')
                ]));
            } else {
                $previous_page = session()->get("previous_path");
                if ($previous_page != $path) {
                    self::newRequest($ip);
                    session()->set("previous_path", $path);
                } elseif(strtotime($array->request) <= strtotime('-2 minutes')) {
                    self::newRequest($ip);
                    $array->request = date('H:i:s');
                    session()->set('request_log', json_encode($array));
                }
            }
        }

        return true;
    }

    private static function newSession($ip) {
        $GeoIP = GeoIP::get($ip);
        DB::table('user_log')->insert([
            'ip' => $ip,
            'country' => $GeoIP->country_code,
            'requests' => 1
        ]);
    }

    private static function newRequest($ip) {
        DB::table('user_log')
            ->take(1)
            ->where('ip', '=', $ip)
            ->where(DB::raw('created_at=CURDATE()'))
            ->update([
                DB::raw('requests=requests+1')
            ]);
    }

}