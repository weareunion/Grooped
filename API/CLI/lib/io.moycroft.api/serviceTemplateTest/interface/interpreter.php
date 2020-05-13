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
        Service: Registration
        Author: Moycroft
        Description:This library is responsible for the registration of CLI services. 
                For account registration use the 'acc' service. 
        Access: 'reg' or 'registration'\n 
        Commands:  
                    index: Reindex binaries and command scripts.
                    lookup <commandName>: lookup if command name is registered.");
}elseif ($command[1] == "version" || $command[1] == "-v"){
    CLIEcho("v1.0.0");
}else{
    \Moycroft\CLI\formatting\response\CLIDie("The command '$command[1]' does not exist. Type 'command help' for help.", true);
}