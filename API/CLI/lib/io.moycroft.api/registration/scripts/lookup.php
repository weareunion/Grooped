<?php
namespace Moycroft\CLI\registration\scripts\lookup;
require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
require_once dirname(__FILE__) . "/../classes/Registration.php";
use function Moycroft\CLI\formatting\response\CLIEcho;
use Moycroft\CLI\registration\Registration;


function lookup($service){
    $reg = new Registration();
    $res = $reg->lookup($service);
    if (!$res){
        CLIEcho("This service does not exist.", "warning");
    }else{
        CLIEcho("COMMAND LOOK UP RETURNED 1 RESULT: \n Known as: $service \n Alias: ".$res['alias']."\n Provider: ".$res['provider']."\n Service: ".$res['service']."\n", "success");
    }
}