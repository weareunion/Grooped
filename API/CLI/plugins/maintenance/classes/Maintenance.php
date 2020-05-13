<?php


namespace Moycroft\API\internal\maintenance;
require_once dirname(__FILE__) . "/../../mysql/classes/connect.php";
use Moycroft\API\internal\mysql\Connect;



class Maintenance
{
    private $connection;
    public function __construct()
    {
        $this->connection = new Connect();
    }
    public function run(){
        define("FLAG_MAINT_RUN", true);

        //variables

        //Amount of logs to keep - others will be put into a historical log file
        $errorLogDepth = 10;
        $pathForData = "../../data";
        $keepErrorHistoryCount = 3;
        try{
            require_once dirname(__FILE__) . "/../scripts/cleanup/databases.php";
        }catch (\Exception $e){
            echo "[WARNING] Could not run database cleanup.";
        }
    }
    public function report($text){
        echo $text;
    }
}