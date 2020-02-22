<?php
namespace Fontibus\IP;

use Exception;
use Fontibus\Number\Number;

class GeoIP {

    private static array $cache = [];

    /**
     * Get GeoIP instance with Data
     *
     * @param string $ip
     * @return GeoIP
     */
    public static function get(string $ip) {
        if(empty($ip))
            return null;

        if(array_key_exists($ip, self::$cache))
            return self::$cache[$ip];

        $currency_code = env('CURRENCY_CODE', 'EUR');

        try {
            $data =  @unserialize(file_get_contents('http://www.geoplugin.net/php.gp?base_currency=' . $currency_code . '&ip=' . $ip));
        } catch(Exception $e) {
            $data = null;
        }

        if(empty($data)) {
            self::$cache[$ip] = null;
            return null;
        }

        foreach ($data as $key => $value)
            if (is_null($value))
                $data[$key] = '';

        $class = new GeoIP($data);
        self::$cache[$ip] = $class;
        return $class;
    }

    public string $ip;
    public string $city;
    public string $region_code;
    public string $region_name;
    public string $country_code;
    public string $country_name;
    public float $long;
    public float $lat;
    public string $currency_code;
    public string $currency_symbol;
    public float $currency_conversion;

    public function __construct(array $data) {
        $this->ip = $data['geoplugin_request'];
        $this->city = $data['geoplugin_city'];
        $this->region_code = $data['geoplugin_regionCode'];
        $this->region_name = $data['geoplugin_regionName'];
        $this->country_code = $data['geoplugin_countryCode'];
        $this->country_name = $data['geoplugin_countryName'];
        $this->long = floatval($data['geoplugin_longitude']);
        $this->lat = floatval($data['geoplugin_latitude']);
        $this->currency_code = $data['geoplugin_currencyCode'];
        $this->currency_symbol = $data['geoplugin_currencySymbol_UTF8'];
        $this->currency_conversion = $data['geoplugin_currencyConverter'];
    }

    public function convert(float $price): float {
        return $price * $this->currency_conversion;
    }

    public function distance(float $long, float $lat, string $decimal_point = ',', string $thousands_step = '.'): string {
        return number_format(Number::distance($this->long, $this->lat, $long, $lat), 2, $decimal_point, $thousands_step);
    }

}