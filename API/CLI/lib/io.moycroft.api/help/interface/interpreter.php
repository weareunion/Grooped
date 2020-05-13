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
if (!isset($command[1])){
    CLIEcho( "Welcome to the Moycroft CLI!");
    CLIEcho( "To get started, type a command. If you are developer, you can access the documentation for the built in packages in your developer portal.");
    CLIEcho( "[How to Install] If you want to install a package, just type 'install <file>'.");
    CLIEcho( "[Access History] To show Moycroft's CLI command history, type 'history'. To execute the command, type 'history <entry>', where entry is the command's number.");
    CLIEcho( "[Get System Information] If you want to get system information, type 'info <command>'. For version type 'info -v'");
    CLIEcho( "-------------------------------------------------------------");
    CLIEcho( "Usage: help");
}