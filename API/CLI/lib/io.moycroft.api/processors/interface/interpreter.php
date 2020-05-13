<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

/**
 * This is the standard CLI interpret script.
 * The $command variable contains the command that was sent split into an array based on spaces.
*/
namespace Moycroft\CLI\processors\intfce\interpreter;
error_reporting(E_ERROR | E_PARSE);
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
use function Moycroft\CLI\processors\activity\show_activity;
use function Moycroft\CLI\processors\functions\set\chron\run;
use function Moycroft\CLI\processors\lst\queue\show_queue;

if (!defined("IN_WEB_INTERFACE")){
    define("REPORTING_IN_POST", true);
    define("CONFIG_PREVENTIMPORTS", true);
    define("CONFIG_RUNASVERBOSE", false);
    define("PROCESSOR_MODE", true);
}


$_SESSION['internal.API.dev.verbose'] = false;
error_reporting(E_ERROR | E_PARSE);
require_once __DIR__ ."/../../../../../config.php";
require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
if (!isset($command)){
    \Moycroft\CLI\formatting\response\CLIDie("Command has not been passed.", true);
}
if (!isset($command[1])){
    \Moycroft\CLI\formatting\response\CLIDie("You are missing required parameters. Type 'processors help' for more information.");
}
if ($command[1] == "help"){
        CLIEcho("
        Service: Processors
        Author: Moycroft
        Description: This library is responsible for the registration of switchboard services. This command only modifies dynamic processors and will not affect static hardcoded methods.
        Access: 'processors' or 'process'\n 
        Commands:  
                    processors set <destination> <service> <action> <processor>: Make a processor known.
                    processors unset [<destination> <service> || <processor>]: Make a processor unknown.
                    processors enable/disable processors help
                     [<destination> <service> || <processor>]: Enable or disable a processor. 
                    processors list: List all known processors.
                    processors list queue: List scheduled and queued activity.
                    processors log [count]: Show logs.
                    processors global suspend [length]: Prevent execution of commands for n seconds. Be aware that this command will cause downtime and the system will attempt to process any new actions when the suspension is lifted.  
                    processors clear queue");
} elseif ($command[1] == "version" || $command[1] == "-v") {
    CLIEcho("v1.0.0");
} elseif ($command[1] == "set"){
    if (!isset($command[2])){
        \Moycroft\CLI\formatting\response\CLIDie("You must specify a target");
    }
    switch (strtoupper($command[2])){
        case "CHRON":
        case "CRON":
        case "CLI":
        case "POST":
            if (!isset($command[3], $command[4], $command[5])){
                CLIDie("You're missing parameters. Format: 'processors set <route> <service> <action> <processor>'.");
            }

            require_once __DIR__ . "/../functions/set/chron/run.php";
            run($command[3], $command[4], $command[5],strtoupper($command[2]));
            break;
        default:
            \Moycroft\CLI\formatting\response\CLIDie("Invalid route. A valid route would be: (CHRON, CRON, CLI, POST)");
            break;
    }
} elseif ($command[1] == "enable" || $command[1] == "disable"){
    switch (strtoupper($command[2])){
        case "CHRON":
        case "CRON":
        case "CLI":
        case "POST":
            if (!isset($command[3])){
                CLIDie("You're missing parameters. Format: 'processors unset [<route> <service> || <processor>]'.");
            }
            require_once __DIR__ . "/../functions/toggle/chron/run.php";
            $status = $command[1] == "enable" ? 1 : 0;
            if (!isset($command[4])){
                \Moycroft\CLI\processors\functions\toggle\chron\run(null, null, $command[3], $status, strtoupper($command[2]));
            }else{
                \Moycroft\CLI\processors\functions\toggle\chron\run($command[3], $command[4], null, $status, strtoupper($command[2]));
            }

            break;
        default:
            \Moycroft\CLI\formatting\response\CLIDie("Invalid route. A valid route would be: (CHRON, CRON, CLI, POST)");
            break;
    }
} elseif ($command[1] == "unset"){
    switch (strtoupper($command[2])){
        case "CHRON":
        case "CRON":
        case "CLI":
        case "POST":
            if (!isset($command[3])){
                CLIDie("You're missing parameters. Format: 'processors unset [<route> <service> || <processor>]'.");
            }
            require_once __DIR__ . "/../functions/unset/chron/run.php";
            if (!isset($command[4])){
                \Moycroft\CLI\processors\functions\ust\chron\run(null, null, $command[3], strtoupper($command[2]));
            }else{
                \Moycroft\CLI\processors\functions\ust\chron\run($command[3], $command[4], strtoupper($command[2]));
            }

            break;
        default:
            \Moycroft\CLI\formatting\response\CLIDie("Invalid route. A valid route would be: (CHRON, CRON, CLI, POST)");
            break;
    }
} elseif ($command[1] == "activity" || $command[1] == "log" ){
    require_once __DIR__ . "/../functions/activities/list.php";
    show_activity((isset($command[2]) ? $command[2] : 5));

}elseif ($command[1] == "list"){
    if (isset($command[2]) && $command[2] == "queue"){
        require_once __DIR__ . "/../functions/list/queue.php";
        show_queue(20);
    }else{
        require_once __DIR__ . "/../functions/list/run.php";
        \Moycroft\CLI\processors\functions\lst\run();
    }
} elseif ($command[1] == "global"){
    if (isset($command[2]) && $command[2] == "suspend"){
        if (isset($command[3]) && intval($command[3]) && $command[3] >= 0){
            try{
                $file = fopen(__DIR__."/../data/suspension.globalpref", "w");
                fwrite($file, time()+$command[3]);
                fclose($file);
                CLIEcho("Suspension activated for " . $command[3] . "s until " . date('Y-m-d h:i:s',((time()+$command[3]))), "success");
            }catch (\Exception $e){
                CLIDie("Suspension could not be activated.");
            }
        }else{
            CLIDie("Third Parameter must be a number greater than or equal to 0");
        }


    }else{
        CLIDie("This global action is not available.");
    }

}else {
    \Moycroft\CLI\formatting\response\CLIDie("The command '$command[1]' does not exist. Type 'command help' for help.", true);
}