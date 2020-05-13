<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-23
 * Time: 16:48
 */

namespace Moycroft\API\util\time;


use function Moycroft\API\internal\reporting\report\__infoSH;

class Time
{
    public static function timeago($datetime, $prefix="about") {
        __infoSH("Calculating human readable timeago from UNIX: " . var_export($datetime, true));
        $time_difference = time() - $datetime;
        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return $prefix .' ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
}