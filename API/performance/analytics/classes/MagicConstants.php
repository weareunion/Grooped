<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\performance\analytics\magic\Constants;
use Moycroft\API\performance\analytics\CPU\CPU;
use Moycroft\API\performance\analytics\Disk\Disk;
use Moycroft\API\performance\analytics\memory\Memory;
use Moycroft\API\performance\analytics\other\System\System;
use function Moycroft\API\helper\IMPORT;

IMPORT("API.performance.analytics.CPU");
IMPORT("API.performance.analytics.Memory");

class MagicConstants
{
    static function compute(){
        $cpu = CPU::getCurrentLoad();
        $memory = Memory::getCurrentLoad();
        $disk = Disk::getCurrentLoad();
        $http = System::getHTTPLoad();
        $preeval = ($memory*.5) + ($cpu*.3) + ($disk*.1) + ($http*.1);
        if ($preeval < .2) return $preeval;
        return sqrt($preeval-.2);
    }
    static function loadBalancer($minimum, $maximum,$invert=false,$asFloat=true, $allowThreshold=false, $threshold=.8){
        if ($allowThreshold && self::compute()>=$threshold) return false;
        $figure = $invert ? ((self::compute())*($maximum-$minimum) + $minimum) : (1-self::compute())*($maximum-$minimum) + $minimum;
       return $asFloat ? $figure : floor($figure);
    }
}