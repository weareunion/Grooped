<?php
namespace Moycroft\CLI\registration\scripts\delete;
require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
require_once dirname(__FILE__) . "/../classes/Registration.php";
use function Moycroft\CLI\formatting\response\CLIEcho;
use Moycroft\CLI\registration\Registration;


function delete($service)
{
    $reg = new Registration();
    $reg->remove($service);
}