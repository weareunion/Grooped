<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
namespace Moycroft\API\internal\Chron;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\reporting\report\__crash;

\Moycroft\API\helper\IMPORT("API.internal.mysql.*");
\Moycroft\API\helper\IMPORT("API.internal.GUID.*");
class Chron
{

    static function add($time, $service, $action, $recurring=false, $recurrcount=0, $data='', $allowDuplicates=false, $to='', $from=null, $delay=0){

        $allowDuplicates = $allowDuplicates ? 1 : 0;
        $recurring = $recurring ? 1 : 0;
        if ($from == null) {
            $from = null;
            if (!$from) {
                session_start();
                if (!isset($_SESSION['user_account'])) {
                    __crash("Session must be set and logged in user must be present");
                    return false;
                } else {
                    $from = $_SESSION['user_account'];
                }
            }
        }

        $UIDS = base64_encode("".(round($time/3600)*3600).$service.$action.$recurring.$recurrcount.$data.$allowDuplicates.$to.$from.$delay);

        $allow = true;

        if (!$allowDuplicates){
                $resp = (self::getConnectedSQLObject()->query("SELECT * FROM `internal_chron_listed` WHERE `UIDS` = '$UIDS'", true));
                var_dump($resp);
                $allow = sizeof($resp) == 0 ? true : false;
        }

        if (!$allow){ return false;}
        $id = GUID("API.internal.processor", "Processor Cron task", false);

        return self::getConnectedSQLObject()->query("INSERT INTO internal_chron_listed (`chron_ID`,`service`,`action`,`trigger`,`data`,`allowDuplicates`,`requester_ID`,`recipient_id`,`recurring`,`run_limit`, `UIDS`, `delay`) VALUES ('$id','$service','$action','$time','$data','$allowDuplicates','$from','$to','$recurring','$recurrcount','$UIDS', '$delay');");
    }
    static function resolve($ID, $recurring, $left=0, $newTime=0, $status="No Status"){
//        echo "Resolving: $ID $status \n";
        $descript = " - " . $status ." -  Task is one-use and will be deleted.";
        if (!$recurring){
            self::delete($ID);
        }else{
            $left--;
            if ($left < 0){
                self::delete($ID);
            }else{
                self::getConnectedSQLObject()->query("UPDATE `internal_chron_listed` SET `run_limit` = '$left', `trigger` = '$newTime';");
                $descript = " - " . $status ." - Task will expire in $left run(s).";
            }

        }
        self::addLog($ID, "200", "Run Completed." . $descript);
    }
    static function delete($ID){
//        echo "Deleting: $ID";
        self::getConnectedSQLObject()->query("DELETE FROM `internal_chron_listed` WHERE `chron_ID` = '$ID';");
        self::addLog($ID, "100", "Task has expired and will be deleted.");
    }
    static function get($ID=null){
        if ($ID != null){
            $val = self::getConnectedSQLObject()->query("SELECT * FROM `internal_chron_listed` WHERE chron_ID = '$ID'", true);
            return (sizeof($val) == 0) ? false : $val;
        }else {
            $time = time()+59;
            return self::getConnectedSQLObject()->query("SELECT * FROM `internal_chron_listed` WHERE `trigger` < '$time'", true);
        }
    }
    static function process(){

        $events = self::get();
        foreach ($events as $event){
            if ($event["recurring"] == 1 && $event["recurring"] < 0){
//                echo "Resolving: " . $event['chron_ID'];
                self::resolve($event['chron_ID'], $event["recurring"], $event['run_limit'], $event['trigger']+$event['delay']);
                continue;
            }
            $location = __DIR__ ."/../processors"."/". $event['service']."/".$event["action"] ."/CRON/run.php";
            if(!file_exists($location)){
                self::addLog($event['chron_ID'], "404", "The task could not be completed due to there not being a processor available. @ " . __DIR__ ."/../processors" ."/". $event['service']."/".$event["action"] .".php");
                continue;
            }
            require_once $location;
//            echo $event['service'];
            $status = \Moycroft\API\internal\Chron\processor\process($event['service'], $event["action"], $event["data"], $event["recipient_id"] , $event["requester_ID"]);

            if($status[0] == true){
                self::resolve($event['chron_ID'], $event["recurring"], $event['run_limit'], $event['trigger']+$event['delay'], $status[1]);
            }else{
                self::addLog($event['chron_ID'], "400", "There was an error with the script: `".$status[1]."` Retrying...");
            }

        }
    }
    static function getLogs($count=10){
        return self::getConnectedSQLObject()->query("SELECT * FROM internal_chron_log ORDER BY `execution_timestamp` DESC LIMIT $count;", true);
    }
    static function isSuspended(){
        $susp_file = __DIR__ . "/../../../CLI/lib/io.moycroft.api/processors/data/suspension.globalpref";
        if (!file_exists($susp_file)){
            return false;
        }
     
        if (intval(file_get_contents($susp_file)) < time()){
            return false;
        }
        return true;
    }
    static function addLog($ID, $status, $status_message){

        self::getConnectedSQLObject()->query("DELETE FROM internal_chron_log WHERE `chron_ID` = '$ID' AND `status` = '$status' AND `staus_message` = '$status_message'");
        self::getConnectedSQLObject()->query("INSERT INTO internal_chron_log (`chron_ID`, `status`, `staus_message`, `execution_timestamp`) VALUES ('$ID', '$status', '$status_message', NOW())", false);

    }
    static function getConnectedSQLObject(){
        $connection = new Connect();
        $connection->connect();
        return $connection;
    }
}