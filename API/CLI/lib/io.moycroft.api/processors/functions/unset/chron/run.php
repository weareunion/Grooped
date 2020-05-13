<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */


namespace Moycroft\CLI\processors\functions\ust\chron;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;


require_once __DIR__ ."/../../../../../../../internal/mysql/classes/connect.php";
function run( $service=null, $action=null, $processor=null, $route=null){
    $connect = new \Moycroft\API\internal\mysql\Connect();
    $connect->connect();
    if ($processor != null){
        if (!$connect->query("SELECT `processor` FROM `internal_chron_processors` WHERE `route` = '$route' AND `processor` = '$processor';", true)){
            CLIDie("This processor has not been associated to a case.");
        }else{
            $connect->query("DELETE FROM `internal_chron_processors` WHERE `route` = '$route' AND `processor` = '$processor';");
        }
    }
    if ($action != null && $service != null){
        if (!$connect->query("SELECT `processor` FROM `internal_chron_processors` WHERE `route` = '$route' AND `service` = '$service' AND `action` = '$action';", true)){
            CLIDie("This service and action group does not have a processor set.");
        }else{
            $connect->query("DELETE FROM `internal_chron_processors` WHERE `route` = '$route' AND `service` = '$service' AND `action` = '$action';");
        }
    }
}