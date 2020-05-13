<?php

namespace Moycroft\CLI\processors\lst\queue;

use function Moycroft\API\helper\IMPORT;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;

IMPORT("API.internal.processor.*");
IMPORT("API.internal.mysql.*");

function show_queue($amount = 5)
{
    CLIEcho("Retrieving Processor Logs... ");
    if (!isset($amount) || $amount == null) {
        $amount = 5;
    }
    $c = new \Moycroft\API\internal\mysql\Connect();
    $c->connect();
    CLIEcho("=================================Scheduled Activity=================================");
    CLIEcho("  ");
    CLIEcho("------------------------------------------------------------------------------------");
    CLIEcho("|  Sequence Number  |  Cron ID  |  Service  | Action |  Trigger  |  Requester ID  | Recipient ID | Recurring | Run Limit | Delay | Data |");
    CLIEcho("------------------------------------------------------------------------------------");
    $logs = $c->query("SELECT * FROM internal_chron_listed ORDER BY table_SEQUENCE ASC LIMIT $amount;", true);
    if(sizeof($logs) == 0){
        CLIEcho("No scheduled activities available.");
    }
    foreach ($logs as $log){
        CLIEcho(sprintf("| %s | %s | %s | %s | %s | %s | %s | %s | %s | %s | %s |",
            $log['table_SEQUENCE'],
            $log["chron_ID"],
            $log["service"],
            $log["action"],
            $log['trigger'],
            $log["requester_ID"],
            $log["recipient_ID"],
            $log["recurring"],
            $log["run_limit"],
            $log["delay"],
            $log["data"]
        ));
    }




}



