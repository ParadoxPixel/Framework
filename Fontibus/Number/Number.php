<?php
namespace Fontibus\Number;

class Number {

    /**
     * Calculate difference between numbers and make absolute if required
     * @param float $number_1
     * @param float $number_2
     * @param bool $absolute
     * @return float
     */
    public static function difference(float $number_1, float $number_2, bool $absolute = true): float {
        $difference = $number_1 - $number_2;
        if($absolute)
            return abs($difference);

        return $difference;
    }

    /**
     * Distance between point A and B in kilometers
     * @param float $lon1
     * @param float $lat1
     * @param float $lon2
     * @param float $lat2
     * @return float
     */
    public static function distance(float $lon1, float $lat1, float $lon2, float $lat2): float {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lon1 *= $pi80;
        $lat2 *= $pi80;
        $lon2 *= $pi80;

        $r = 6372.797;
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $r * $c;
    }

}