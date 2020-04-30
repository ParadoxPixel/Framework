<?php
namespace Fontibus\IP;

class IP {

    /**
     * Makes sure the IP used is the one of the user not of the proxy
     * @return void
     */
    public static function init(): void {
        if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $ip  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } elseif($ip === '::1') {
            $ip = '127.0.0.1';
        }

        $_SERVER['REMOTE_ADDR'] = $ip;
    }

}