<?php
/**
 * This is the standard CLI interpret script.
 * The $command variable contains the command that was sent split into an array based on spaces.
*/
//namespace #provider#\CLI\#service#\intfce\interpreter
use function Moycroft\CLI\formatting\response\CLIEcho;

require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
if (!isset($command)){
    \Moycroft\CLI\formatting\response\CLIDie("Command has not been passed.", true);
}
if ($command[1] == "help"){
        CLIEcho("
        Test Platform");
}elseif ($command[1] == "run"){
    \Moycroft\API\helper\IMPORT("API.internal.processor.*", true);
    $status = \Moycroft\API\internal\Chron\processor\Processor::getProcessorFromName("permissionProcess", "CRON");
    CLIEcho($status->name);
    if ($status->enabled){
        CLIEcho("Processor Enabled");
    }else{
        CLIEcho("Processor not Enabled");
    }
    if ($status->available){
        CLIEcho("Processor Available");
    }else{
        CLIEcho("Processor not Available");
    }
} elseif ($command[1] == "version" || $command[1] == "-v") {

} else {
    \Moycroft\CLI\formatting\response\CLIDie("The command '$command[1]' does not exist. Type 'command help' for help.", true);
}