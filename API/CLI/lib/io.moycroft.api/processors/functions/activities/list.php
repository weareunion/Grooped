<?php
namespace Moycroft\CLI\processors\activity;

use function Moycroft\API\helper\IMPORT;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
IMPORT("API.internal.processor.*");
IMPORT("API.internal.mysql.*");

function show_activity($amount=5){

    CLIEcho("Retrieving Processor Logs... ");
    if(!isset($amount) || $amount == null){
        $amount = 5;
    }
    $c = new \Moycroft\API\internal\mysql\Connect();
    $c->connect();
    if ($amount == "clear"){
        CLIEcho("Clearing logs...");
        $c->query("DELETE FROM internal_chron_log;");
        die();
    }
    CLIEcho("=================================PROCESSOR ACTIVITY================================="); 
    CLIEcho("  ");
    CLIEcho("------------------------------------------------------------------------------------");
    CLIEcho("|  Sequence Number  |  Cron ID  |  Status Number  |  Status Message  |  Timestamp  |");
    CLIEcho("------------------------------------------------------------------------------------");
    $logs = $c->query("SELECT * FROM internal_chron_log ORDER BY table_SEQUENCE DESC LIMIT $amount;", true);
    if(sizeof($logs) == 0){
        CLIEcho("No logs available.");
    }
    foreach ($logs as $log){
        CLIEcho(sprintf("| %s | %s | %s | %s | %s |", $log['table_SEQUENCE'], $log["chron_ID"], $log["status"], $log["staus_message"], $log["execution_timestamp"]));
    }


}
 ?>


