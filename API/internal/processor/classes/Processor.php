<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\internal\Chron\processor;
use Moycroft\API\internal\Chron\Chron;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\processor\EXECUTABLE\run;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;

    \Moycroft\API\helper\IMPORT("API.internal.mysql.*", true);
    \Moycroft\API\helper\IMPORT("API.internal.processor.Chron", true);
    \Moycroft\API\helper\IMPORT("API.internal.GUID.*", true);


class Processor
{


    /**
     * @var Connect - Connection to database
     */
    private static $connection;
    public $name, $service, $action, $request_ID, $available, $available_routes, $enabled, $exists = null;

    static function createConnection(){
        self::$connection = new Connect();
        self::$connection->connect();
    }
    function processorAvailable(){
        if (Chron::isSuspended()) return false;
        return $this->enabled == 1;
    }
    static function getProcessorFromName($name, $route){
        self::createConnection();
        $ext_proc = self::$connection->query("SELECT * FROM `internal_chron_processors` WHERE UPPER(processor) LIKE '$name' AND UPPER(route) LIKE '$route';", true);
        if (sizeof($ext_proc) == 0) {
            return self::getProcessorFromParameters("", "", "", true);
            }
        $ext_proc = $ext_proc[0];
        return self::getProcessorFromParameters($ext_proc["service"], $ext_proc["action"], $ext_proc["route"]);
    }

    function run($data){
        $supposed_location = __DIR__ . "/../processors/$this->service/$this->action/$this->available_routes/run.php";
        if (!file_exists($supposed_location)){
            $this->panic("Processor script does not exist. Script expected at $supposed_location.");
        }
        if (!is_readable($supposed_location)){
            $this->panic("Processor script is not readable.");
        }
        require_once $supposed_location;
        run($data);

    }
    function isValidPHP($str) {
        return trim(shell_exec("echo " . escapeshellarg($str) . " | php -l")) == "No syntax errors detected in -";
    }
    function getRequestID(){
        if ($this->request_ID == null) {
            $this->request_ID = GUID("API.internal.processor", "REQUEST @ $this->service $this->action :: $this->name", true);
        }
        return $this->request_ID;
    }

    function panic($reason){
        self::$connection->query("UPDATE `internal_chron_processors` SET `enabled` = '0' WHERE UPPER(processor) LIKE '$this->name' AND UPPER(route) LIKE '$this->available_routes' ");
        $this->addLog($this->getRequestID(), "404", "CRITICAL: Processor disabled due to \"$reason\". This processor cannot be enabled again until this error has been resolved.");
        throw new Panic();
    }

    function addLog($ID, $status, $status_message){
        self::createConnection();
        self::$connection->query("DELETE FROM internal_chron_log WHERE `chron_ID` = '$ID' AND `status` = '$status' AND `staus_message` = '$status_message'");
        self::$connection->query("INSERT INTO internal_chron_log (`chron_ID`, `status`, `staus_message`, `execution_timestamp`) VALUES ('$ID', '$status', '$status_message', NOW())", false);

    }

    static function getProcessorFromParameters($service, $action, $route, $nullObject = false){
        self::createConnection();
        $action = strtoupper($action);
        $service = strtoupper($service);
        $route = strtoupper($route);
        $ext_proc = self::$connection->query("SELECT * FROM `internal_chron_processors` WHERE UPPER(action) LIKE '$action' AND UPPER(service) LIKE '%$service%' AND UPPER(route) LIKE '$route';", true);
        $returning = new Processor();
        if (sizeof($ext_proc) == 0 || $nullObject){

            $returning->service = $service;
            $returning->action = $action;
            $returning->available_routes = [];
            $returning->exists = false;
            $returning->available = false;
            return $returning;
        }
        $ext_proc = $ext_proc[0];
        $returning->service = $ext_proc['service'];
        $returning->action = $ext_proc['action'];
        $returning->available_routes = $ext_proc['route'];
        $returning->name = $ext_proc['processor'];
        $returning->exists = true;
        $returning->enabled = $ext_proc['enabled'];
        $returning->available = $returning->processorAvailable();
        return $returning;
    }
}


class UnknownProcessor extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($name, $code = 404, \Exception $previous = null) {
        $message = "The Processor $name does not exist.";
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class ProcessFailed extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($name, $code = 400, \Exception $previous = null) {
        $message = "The Processor failed to execute. '$name'";
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class Panic extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($code = 100, \Exception $previous = null) {
        $message = "The Processor Manager has panicked. Check processor logs for more information.";
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}