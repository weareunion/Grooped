<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\performance\analytics\CPU;


class CPU
{
    static function getLoadAverage(){
        $a = sys_getloadavg();
        if(count($a)) {
            $a = array_filter($a);
            return array_sum($a)/count($a);
        }
    }

    static function getCurrentLoad(){
        return self::getLoadAverage();
    }
}