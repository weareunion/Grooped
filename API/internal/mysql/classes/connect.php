<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-13
 * Time: 21:40
 */

namespace Moycroft\API\internal\mysql;
//use function Moycroft\API\helper\IMPORT;
//
//require_once "../auth/auth.php";
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\reporting\report\__crash;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;

session_start();

$verbose = false;
if(isset($_SESSION['internal.API.dev.verbose']) && $_SESSION['internal.API.dev.verbose']){
    $verbose = true;
}
require_once dirname(__FILE__) . "/../auth/auth.php";
IMPORT("API.internal.reporting.*");
class Connect
{
    public $silent = false;
    public function __construct($silent=false)
    {
        $this->silent = $silent;
    }

    private $conn;
    /**
     * Attempts to create and return mysql connection
     * @param $database - name of database
     * @return connection
    */
    public function connect($database=null){
        global $verbose;
        $prefix = "washmelc_moycroft_";
        if (($database == null)){
            $database = DB_NAME;
        }
//        echo $database
        $this->conn = mysqli_connect(DB_HOST,DB_USER,DB_PASS, $database);
        if ($this->conn->connect_error) {
            __crash("Connection failed: " . $this->conn->connect_error);
        }else{
//            $this->query("USE moycroft_global;");
            if (mysqli_connect_errno())
            {
                __error( "Failed to connect to MySQL: " . mysqli_connect_error(), true);
                return null;
            }
               __infoSH("Connection to database has been established", "success");

            return $this->conn;
        }
    }
    /**
     * Attempts to disconnect mysql connection
    */
    public function disconnect(){
        mysqli_close($this->conn);
//        __infoSH("Connection disconnected successfully.", "success");
    }
    /**
     * Attempts to run given query
    */
    public function query($query, $pullData=false){
        $this->autoConnect();
        global $verbose;
        $resp = mysqli_query($this->conn, $query);
        if ($resp) {
            if (!$this->silent) {
                __infoSH("Query ($query) executed successfully.", "success");
            }
            if ($pullData){
                return $this->getData($resp);
            }else {
                return $resp;
            }
        } else {
           __error( "Error: " . $query . "<br>" . mysqli_error($this->conn), false);
            return false;
        }
    }
    private function autoConnect(){
        if ($this->conn == null){
            $this->connect();
        }
    }
    public function cleanString($string){
        $this->autoConnect();
        return mysqli_escape_string($this->conn, $string);
    }
    /**
     * Attempts to return all data from response that is fed in the form of an array
    */
    public function getData($resp){
        $this->autoConnect();
        $queryResponse = [];
            while($row = mysqli_fetch_assoc($resp)) {
                array_push($queryResponse, $row);
            }
            return $queryResponse;
    }

    public function getConn(){
        $this->autoConnect();
        return $this->conn;
    }

    static $logCount = 0;

}