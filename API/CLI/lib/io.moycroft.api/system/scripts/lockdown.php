<?php

namespace Moycroft\CLI\system\lockdown;

use function Moycroft\CLI\formatting\response\CLIEcho;

function set()
{
    file_put_contents(str_replace(basename(__FILE__), "", __FILE__) . "/../data/lockdown/status", "1");
    \Moycroft\CLI\formatting\response\CLIEcho("Lockdown has started.", "success");
    CLIEcho("All users except developers have been locked out of the system.");
    CLIEcho("To terminate type 'system lockdown lift'");
}

function lift()
{
    file_put_contents(str_replace(basename(__FILE__), "", __FILE__) . "/../data/lockdown/status", "0");
    \Moycroft\CLI\formatting\response\CLIEcho("Lockdown has been lifted.", "success");
}
function status()
{
    $st = file_get_contents(str_replace(basename(__FILE__), "", __FILE__) . "/../data/lockdown/status");
    if ($st){
        CLIEcho("Lockdown Status: Active", "info");
    }else{
        CLIEcho("Lockdown Status: Inactive", "info");
    }
}