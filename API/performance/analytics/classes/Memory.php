<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\performance\analytics\memory;


class Memory
{
    /**
     * @return array Array of system memory information
     */
    static function getSystemMemInfo()
    {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $meminfo[$key] = trim($val);
        }
        return $meminfo;
    }

    /**
     * @return float|int returns float 0 to 1 of usage (zero-low)
     */
    static function getCurrentLoad(){
        $data = self::getSystemMemInfo();
        return ((float) $data["MemTotal"] - (float) $data['MemAvailable']) / (float) $data["MemTotal"];
    }
    static function getSystemMemory()
    {
        $data = self::getSystemMemInfo();
        return $data["MemTotal"];
    }
    static function getAvailableMemory()
    {
        $data = self::getSystemMemInfo();
        return $data["MemAvailable"];
    }

}