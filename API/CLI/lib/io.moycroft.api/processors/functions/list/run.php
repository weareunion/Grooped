<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
namespace Moycroft\CLI\processors\functions\lst;

use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;

require_once __DIR__ ."/../../../../../../internal/mysql/classes/connect.php";
function run(){
    $connect = new \Moycroft\API\internal\mysql\Connect();
    $connect->connect();
    \Moycroft\CLI\formatting\response\CLIEcho("Processors known to Moycroft", "info");
    \Moycroft\CLI\formatting\response\CLIEcho("CHRON/CRON Processors:");
    $chron = $connect->query("SELECT * FROM `internal_chron_processors` WHERE `route` = 'CHRON' OR `route` = 'CRON';", true);
    if (!$chron){
        CLIEcho("None Known.");
    }else{
         \Moycroft\CLI\formatting\response\CLIEcho("(SERVICE) - (ACTION) - (PROCESSOR) - (ENABLED)");
        foreach ($chron as $processor){
           \Moycroft\CLI\formatting\response\CLIEcho(" -> " . $processor['service'] . " - " . $processor['action'] . " - " . $processor['processor'] . " - " . ($processor['enabled'] ? "Enabled" : "Disabled"));
        }
    }

    CLIEcho("");
    \Moycroft\CLI\formatting\response\CLIEcho("CLI Processors:");
    $CLI = $connect->query("SELECT * FROM `internal_chron_processors` WHERE `route` = 'CLI' ;", true);
    if (!$CLI){
        CLIEcho("None Known.");
    }else{
        \Moycroft\CLI\formatting\response\CLIEcho("(SERVICE) - (ACTION) - (PROCESSOR) - (ENABLED)");
        foreach ($CLI as $processor){
            \Moycroft\CLI\formatting\response\CLIEcho(" -> " . $processor['service'] . " - " . $processor['action'] . " - " . $processor['processor'] . " - " . ($processor['enabled'] ? "Enabled" : "Disabled"));
        }
    }

    CLIEcho("");
    \Moycroft\CLI\formatting\response\CLIEcho("POST Processors:");
    $POST = $connect->query("SELECT * FROM `internal_chron_processors` WHERE `route` = 'POST' ;", true);
    if (!$POST){
        CLIEcho("None Known.");
    }else{
        \Moycroft\CLI\formatting\response\CLIEcho("(SERVICE) - (ACTION) - (PROCESSOR) - (ENABLED)");
        foreach ($POST as $processor){
            \Moycroft\CLI\formatting\response\CLIEcho(" -> " . $processor['service'] . " - " . $processor['action'] . " - " . $processor['processor'] . " - " . ($processor['enabled'] ? "Enabled" : "Disabled"));
        }
    }
}