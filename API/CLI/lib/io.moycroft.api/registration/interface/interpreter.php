<?php
/**
 * This is the standard CLI interpret script.
 * The $command variable contains the command that was sent split into an array based on spaces.
*/
namespace Moycroft\CLI\registration\intfce\interpreter;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
use function Moycroft\CLI\registration\scripts\index\indexBin;
use function Moycroft\CLI\registration\scripts\lookup\delete;
use function Moycroft\CLI\registration\scripts\lookup\lookup;

require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
if (!isset($command)){
    \Moycroft\CLI\formatting\response\CLIDie("Command has not been passed.", true);
}
switch ($command[1]){
    case "help":
        CLIEcho("
        Service: Registration
        Author: Moycroft
        Description:This library is responsible for the registration of CLI services. 
                For account registration use the 'acc' service. 
        Access: 'reg' or 'registration'\n 
        Commands:  
                    index: Reindex binaries and command scripts.
                    lookup <commandName>: lookup if command name is registered.");
        break;
    case "index":
        require_once dirname(__FILE__) . "/../scripts/index.php";
        indexBin();
        break;
    case "unset":
        require_once dirname(__FILE__) . "/../scripts/unset.php";
        \Moycroft\CLI\registration\scripts\delete\delete($command[2]);
        break;
    case "lookup":
        if(!isset($command[2])){
            CLIDie("A service to lookup must be given.", true);
        }else{
            require_once dirname(__FILE__) . "/../scripts/lookup.php";
            lookup($command[2]);
        }
        break;
    default:
        CLIDie("This action does not exist. Type '$command[0] help' for help.");
}
