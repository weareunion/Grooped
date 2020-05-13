<?php
/**
 * This is the standard CLI interpret script.
 * The $command variable contains the command that was sent split into an array based on spaces.
 */

//namespace #provider#\CLI\#service#\intfce\interpreter
use function Moycroft\CLI\formatting\response\CLIEcho;

require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
require_once dirname(__FILE__) . "/../../../../sbin/IO/input/input.php";
if (!isset($command)) {
    \Moycroft\CLI\formatting\response\CLIDie("Command has not been passed.", true);
}
if ($command[1] == "help") {
    CLIEcho("
        Service: System
        Author: Moycroft
        Description: This library is used for system control.
        Access: 'sys' or 'system'\n 
        Commands:  
                    lockdown <lift ~ set: Set or lift lockdown>: Reindex binaries and command scripts.
                    update < -d: destructive update (removes config data, but does not modify user data)>: Get and install latest Moycroft update.
                    lookup <commandName>: lookup if command name is registered.");
} elseif ($command[1] == "lockdown") {
    require_once realpath(str_replace(basename(__FILE__), "", __FILE__) . "/../scripts/lockdown.php");
    if (!isset($command[2])) {
        \Moycroft\CLI\formatting\response\CLIDie("Lockdown must either be set or lift(ed). A second parameter is needed.");
    } elseif ($command[2] == "set") {

        \Moycroft\CLI\system\lockdown\set();

    } elseif ($command[2] == "lift") {
        \Moycroft\CLI\system\lockdown\lift();
    }elseif ($command[2] == "status") {
        \Moycroft\CLI\system\lockdown\status();
    }
} elseif ($command[1] == "update"){
    if (shell_exec('sudo -l | grep "ALL : ALL"') == "" ){
        CLIEcho("You linux user must have sufficient SUDO privileges to execute this command. Acquire these privileges and try again.");
    }
    CLIEcho("We will now attempt to download and install the latest version of Moycroft.");
    CLIEcho("This process will cause a temporary outage and will cause this script to quit. The maintenance page will not be displayed, so it would be recommended to change the routing to a different server. This process will take about 5 minutes. Continue?", "warning");

    if(!\Moycroft\CLI\IO\input\yn()){
        CLIEcho("Aborted", "error");
        exit();
    }
    $resp = shell_exec("sh /var/www/html/moycroft/_installer/update/sh/internal/run.sh");
    if (strpos($resp, "ERROR")!== false){
        $show = explode("\n", $resp);
        echo $show[sizeof($show)-1];
        \Moycroft\CLI\formatting\response\CLIDie("The software could not be installed.");
    }
    CLIEcho("Software has been installed. Moycroft will now attempt to restart...");
    exit(0);
} elseif ($command[1] == "version" || $command[1] == "-v") {
    CLIEcho("v0.0.1");
} else {
    \Moycroft\CLI\formatting\response\CLIDie("The command does not exist. Type 'system help' for help.", true);
}