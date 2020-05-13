<?php
/**
 * This is the standard CLI interpret script.
 * The $command variable contains the command that was sent split into an array based on spaces.
*/
//namespace #provider#\CLI\#service#\intfce\interpreter
use function Moycroft\CLI\formatting\response\CLIEcho;

require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
require_once dirname(__FILE__) . "/../classes/Installer.php";
if (!isset($command)){
    \Moycroft\CLI\formatting\response\CLIDie("Command has not been passed.", true);
}
if ($command[1] == "help"){
    CLIEcho("
        Service: Installation
        Author: Moycroft
        Description:This library is responsible for the registration of CLI services. 
                For account registration use the 'acc' service. 
        Access: 'install'\n 
        Commands:  
                    install -z <zipFile>: Install CLI Component from ZIP file.
                    install <dirToFile>: Install CLI Component from directory");
}
if (!isset($command[1])){
    \Moycroft\CLI\formatting\response\CLIDie("A path for installation is required.");
}
if(isset($command[2]) && $command[1] != "-z"){
    \Moycroft\CLI\formatting\response\CLIDie("Flag does not exist.", true);
}
$installer = new \Moycroft\CLI\install\Installer\Installer();
if (sizeof($command) === 3){
    $installer->install($command[2], true);
}elseif (sizeof($command) === 2){
    $installer->install($command[1], false);
}else{
    CLIEcho("This action does not exist. Type 'install help' for more information.");
}

