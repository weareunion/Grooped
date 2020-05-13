<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\performance\analytics\other\System;


class System
{
    static function getHTTPLoad() {

        if (function_exists('exec')) {

            $www_total_count = 0;
            @exec ('netstat -an | egrep \':80|:443\' | awk \'{print $5}\' | grep -v \':::\*\' |  grep -v \'0.0.0.0\'', $results);

            foreach ($results as $result) {
                $array = explode(':', $result);
                $www_total_count ++;

                if (preg_match('/^::/', $result)) {
                    $ipaddr = $array[3];
                } else {
                    $ipaddr = $array[0];
                }
                $unique = [];
                $www_unique_count = 0;
                if (!in_array($ipaddr, $unique)) {
                    $unique[] = $ipaddr;
                    $www_unique_count ++;
                }
            }

            unset ($results);

            return count($unique)/500;

        }
        return false;
    }
    static function getServerUptime() {

        $uptime = floor(preg_replace ('/\.[0-9]+/', '', file_get_contents('/proc/uptime')) / 86400);

        return $uptime;

    }
}