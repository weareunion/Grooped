<?php
namespace Moycroft\CLI\switchboard;
use Moycroft\API\internal\Chron\processor\Panic;
use Moycroft\API\internal\Chron\processor\ProcessFailed;
use Moycroft\API\internal\Chron\processor\Processor;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
use Moycroft\CLI\registration\Registration;
require_once dirname(__FILE__) ."/lib/io.moycroft.api/registration/classes/Registration.php";
require_once dirname(__FILE__) . "/sbin/formatting/response/scripts/response.php";




if (!defined("IN_WEB_INTERFACE")){
    define("REPORTING_IN_POST", true);
    define("CONFIG_PREVENTIMPORTS", true);
    define("CONFIG_RUNASVERBOSE", false);
}

define("PROCESSOR_MODE", true);
$_SESSION['internal.API.dev.verbose'] = false;
error_reporting(E_ERROR | E_PARSE);
require_once __DIR__ ."/../config.php";

IMPORT("API.internal.mysql.*");
IMPORT("API.internal.processor.*");
if (defined("IN_WEB_INTERFACE")){
    $consoleActive = false;
}else{

    $consoleActive = true;
    register_shutdown_function(function () {
        pcntl_exec("/usr/bin/moycroft", array("silent"));
    });
}
$location = dirname(__FILE__) ."/";




while ($consoleActive) {
    $command = [];
    printf("\n");
    $resSTDIN = fopen("php://stdin", "r");
    echo(">> ");
    $strChar = fgets($resSTDIN);
    fclose($resSTDIN);
    readline_callback_handler_remove();
    $command = explode(" ", str_replace(array("\n", "\r"), '', $strChar));
    try{
        $log = new Connect();

        $user = "Not Available";
        try {
            $user = shell_exec("echo \"\$USER\"");
        }catch (\Exception $e){
            $user = "Could not get user";
        }
//        if (strpos($strChar, "history") !== false){
//            CLIEcho("History commands will not be accessable from history.");
//        }
            $log->connect();
        if (!(strpos($strChar, "history") !== false) && $command[0] != "h" && $command[0] != "hist"){
            $log->query("INSERT INTO `internal_CLI_history` (`command_string`, `user_ID`, `timestamp`) VALUES ('" . str_replace(array("\n", "\r"), '', $strChar) . "','" . str_replace(array("\n", "\r"), '', $user) . "', NOW())");
        }
//

    }catch (\Exception $e){
        CLIEcho("History is not available due to history server not being accessible.", "warning");
    }
    evaluate($command);
}
if (!defined("IN_WEB_INTERFACE")) {
    printf("\n");
}

function evaluate($command){
    if (defined("IN_WEB_INTERFACE")) {
        $strChar = $command;
        $command = explode(" ", str_replace(array("\n", "\r"), '', $strChar));
        try {
            $log = new Connect();
            if (!(strpos($strChar, "history") !== false) && $command[0] != "h" && $command[0] != "hist") {
                session_start();
                $log->query("INSERT INTO `internal_CLI_history` (`command_string`, `user_ID`, `timestamp`) VALUES ('" . str_replace(array("\n", "\r"), '', $strChar) . "','" . str_replace(array("\n", "\r"), '', $_SESSION['user_account'] . "(WEB)") . "', NOW())");
            }
        }catch (\Exception $exception){

        }
    }
    if ($command[0] == "quit") {
        CLIEcho( "Moycroft is now in the background.\n");
        shell_exec("pkill -f /var/www/html/moycroft/dev/API/CLI/switchboard.php");
    }
    if ($command[0] == "reload"){
        die();
    }
    if ($command[0] == "history" || $command[0] == "hist" || $command[0] == "h" || $command[0] == "^[[A"){
        $executable="";
        try{
            $log = new Connect();

                $log->connect();
                $history = $log->query("select * from internal_CLI_history ORDER BY table_SEQUENCE DESC limit 15;", true);
                if (!isset($command[1]) || trim($command[1]) == ""){
                    CLIEcho("=================================History=================================");
                    CLIEcho("  ");
                    CLIEcho("-------------------------------------------------------------------------");
                    CLIEcho("|   Index    |    Number    |    Command    |    User   |   Timestamp   |");
                    CLIEcho("-------------------------------------------------------------------------");
                    foreach ($history as $key=>$item) {
                        CLIEcho(sprintf("[ %s ] \"%s\" from %s @ %s - %s|",
                            $key,
                            $item["command_string"],
                            $item["user_ID"],
                            $item["timestamp"],
                            $item["table_SEQUENCE"]
                        ));
                    }
                }else{
                    $executable= "";
                    try{
                        $executable = $history[$command[1]]["command_string"];
                                if (strpos($executable, "history") !== false){
                                     CLIDie("History commands cannot be run from history.");
                                 }
                    }catch (\Exception $e){
                        $executable = false;
                        CLIDie("Command at this index does not exist. Maximum index is ".sizeof($history).".");
                    }
                }


        }catch (\Exception $e){
            CLIEcho("History is not available due to history server not being accessible.", "warning");
        }
        if ($executable){
            CLIEcho("Running '$executable' from history...");
            evaluate(explode(" ", str_replace(array("\n", "\r"), '', $executable)));
        }
    }else {
        $reg = new Registration();
        $lookup = $reg->lookup($command[0]);
        if (!$lookup) {
            $processor = processor_scan($command[0]);
            if (!$processor) {
                CLIDie("This command does not exist. Type 'help' for help.", true);
            }
            try {
                if (!$processor->processorAvailable()) {
                    CLIDie("This command exists, but is not available at this time. Did it panic and self-disable? You can check the log with 'p log'.");
                }
            }catch (\Throwable $exception){
                CLIDie("This command does not exist. Type 'help' for help.", true);
            }catch (\Exception $exception){
                CLIDie("This command does not exist. Type 'help' for help.", true);
            }
            try {
                try {
                    $processor->run($command);
                } catch (Panic $panic) {
                    CLIDie("The processor has panicked and will suspend any additional requests. You must fix this error before it can be rerun. You can check the log with 'p log'. ");
                }
            }catch (ProcessFailed $exception){
                CLIDie("The command failed to run and returned the following error: '" . $exception->getMessage(). "'.");
            }


        } else {
            $assumedDir = dirname(__FILE__) . "/lib/" . $lookup['provider'] . "/" . $lookup['service'] . "/interface/interpreter.php";
            if (!file_exists($assumedDir)) {
                CLIDie("This service cannot interface with the API properly. Please contact the developer.", true);
            } else {

                try {
                    require($assumedDir);
                } catch (\Exception $exception) {
                    CLIEcho("There was a problem processing the required files for this command. Please contact the developer.", "warning");
                }
            }
        }
    }
}

function processor_scan($name){
    $processor = Processor::getProcessorFromName($name, "CLI");
    if ($processor->exists){
        return $processor;
    }else{
        return false;
    }
}

