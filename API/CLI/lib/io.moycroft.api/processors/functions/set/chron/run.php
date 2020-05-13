<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
namespace Moycroft\CLI\processors\functions\set\chron;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;


require_once __DIR__ ."/../../../../../../../internal/mysql/classes/connect.php";
function run($service, $action, $processor, $route){
    $connect = new \Moycroft\API\internal\mysql\Connect();
    $connect->connect();
    if ($connect->query("SELECT `processor` FROM `internal_chron_processors` WHERE `route` = '$route' AND `service` = '$service' AND `action` = '$action';", true)){
        CLIDie("This service and action group already has a processor associated with it. You must first unset it by typing 'processors unset $service $action'");
    }
    if ($connect->query("SELECT `processor` FROM `internal_chron_processors` WHERE `route` = '$route' AND `processor` = '$processor';", true)){
        CLIDie("This processor name already exists. The name of the processor must be unique.");
    }
    $connect->query("INSERT INTO `internal_chron_processors` (`service`, `action`, `processor`, `enabled`, `route`) VALUES ('$service','$action', '$processor', 1, '$route');");
}