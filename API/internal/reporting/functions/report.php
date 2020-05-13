<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-17
 * Time: 13:48
 */
namespace Moycroft\API\internal\reporting\report;
use Moycroft\API\accounts\Account\Account;
use function Moycroft\API\helper\IMPORT;
    IMPORT("API.internal.*");
    IMPORT("API.UI.*");
;use function Moycroft\API\internal\GUID\GUID;
use Moycroft\API\internal\mysql\Connect;
use Moycroft\API\UI\UI;
use function PHPSTORM_META\elementType;

session_start();
//if (isset($_SESSION['dev.log']) && isset($_SESSION['dev.log'][400]) > 800) {
//    $_SESSION['dev.log'] = null;
//}

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!defined("REPORTING_IN_POST")){

    define("REPORTING_IN_POST", false);
}
if (!isset($_SESSION["dev.log"])){
    $_SESSION["dev.log"] = "";
}
if (strlen($_SESSION["dev.log"]) > 1000000){
    $_SESSION["dev.log"] = substr($_SESSION["dev.log"], -1000000);
    __info("LOG SHORTENED TO LAST 1,000,000 CHARACTERS", "warning");
}
 function __error($error, $crash){
     $verbose = false;

     if ($_SESSION['internal.API.dev.verbose']){
         $verbose = true;
     }
    if (!$verbose) {
        $_SESSION['dev.log'] .= "ERROR: " . $error;
    }
    if ($verbose && !$crash){
        __info($error, "warning");
    }
    if ($crash) {
        __crash($error);
    }
}
 function __crash($error){
     $verbose = false;
     if ($_SESSION['internal.API.dev.verbose']){
         $verbose = true;
     }else{
         if (!REPORTING_IN_POST) {
             ob_clean();
             $UI = new UI();
             die($UI->generateErrorPage(__logReport($error)));
         }
     }
    $color = "white; background-color: orangered";
    if ($verbose){
        $rss = getName(64);
        $andOut = "";
        $_SESSION['dev.log'] .= "<span style=\"color:$color\"><small>[<strong onclick=\"console . log(JSON . parse($rss)); document . getElementById('console') . innerHTML = 'Stack trace in JS Console.' \"> >> ! << </strong>] -> <strong>" ."[ERROR from " . __CLASS__ . " @ " . __DIR__ . "] </strong></small> " . $error . "</span><script>var $rss = '".escapeJsonString(json_encode(debug_backtrace()))."'$andOut</script><br>";
        if (REPORTING_IN_POST){
            __returnHTTPException("API Error", $error);
        }
        die();
    }else{
        if (REPORTING_IN_POST){
            $message = (new Account())->getRank($_SESSION['user_account']) < 2 ? "Check logs for definition." : $error;
            __returnHTTPException("API Error", $message);
        }
        throw new \Error($error);
    }

}
 $logCount = 0;
 function __infoSH($message, $type="info", $function=null){
    __info($message, $type, null, null, $function);
}
function getName($n) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}
function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}
function __info($message, $type="info", $step=null, $totalSteps=null, $function=null)
{
    global $logCount;
    session_start();
    $verbose = false;
    if ($_SESSION['internal.API.dev.verbose']){
        $verbose = true;
    }
    if ($verbose) {
        if ($logCount === 0) {
            $rss = getName(64);
            $andOut = "";
            $_SESSION['dev.log'] .= "<style>body {background-color: #232525;}</style>";
            $_SESSION['dev.log'] .= "<small><strong><span style=\"color:deeppink\">" . "[<strong onclick=\"console . log(JSON . parse($rss)); document . getElementById('console') . innerHTML = 'Stack trace in JS Console.' \"> >> $ << </strong>] -> [INFO from " . __CLASS__ . " @ " . __DIR__ . "] </strong></small><span style=\"color:deeppink\">" . "Launching script" . "</span></span><br><hr style=\"border-color:white; height: 0px;border: 0\"><script>var $rss = '".escapeJsonString(json_encode(debug_backtrace()))."'$andOut</script>";
        }
        $logCount++;
        $color = "deepskyblue";
        $small = "small";
        $messageColor = "darkgray";
        if ($type == "success") {
            $color = "mediumspringgreen";
            $messageColor = "mediumspringgreen";
//            $small = "italic";
        }
        if ($type == "warning") {
            $color = "orange";
            $messageColor = "orange";
        }
        $stepHTML = "";
        if ($step !== null && $totalSteps !== null) {
            $stepHTML = " <strong><u>($step/$totalSteps @ $function() )</u></strong> ";
        }
        if ($step == null && $function != null){
            $stepHTML = " <strong><u>( $function() )</u></strong> ";
        }
        if ($verbose) {
            $rss = getName(64);
            $andOut = "";
            $_SESSION['dev.log'] .=  "<$small><strong onclick=\"console.log(JSON.parse($rss)); document.getElementById('console').innerHTML = 'Stack trace in JS Console.' \"><span style=\"color:$color\">" . "[<strong> >> " . $logCount . " << </strong>] -> [".strtoupper($type)."] </strong></$small><span style=\"color:$messageColor\">$stepHTML" . $message . "</span></span><br><hr style=\"border-color:white; height: 0px;border: 0\"><script>var $rss = '".escapeJsonString(json_encode(debug_backtrace()))."'$andOut</script>";
        }

    }
}
function __logReport($description, $ammendment=null, $clientReport = null)
{
    $connection = new Connect();
    $connection->connect();
    if ($ammendment === null) {
        $reportID = GUID("API.internal.reporting", "Error Report", false);
        $serversideReport = [];
        __infoSH("Starting to generate serverside error report.");
        $serversideReport["message"] = $description;
        $serversideReport["data"]["post"] = $_POST;
        $serversideReport["data"]["session"] = $_SESSION;
        $serversideReport["data"]["cookie"] = $_COOKIE;
        $serversideReport["data"]["environment"] = $_ENV;
        $serversideReport["data"]["get"] = $_GET;
        $serversideReport["data"]["request"] = $_REQUEST;
        $serversideReport["server"] = $_SERVER;
        $serversideReport["stacktrace"] = debug_backtrace();
        $serversideReport["lastError"] = error_get_last();
        $serversideReport["UNIXTime"] = time();
        $serversideReport = json_encode($serversideReport);
        $shortID =  bin2hex(openssl_random_pseudo_bytes(4));
        $connection->query("INSERT INTO `internal_reporting_errorLog` ( `report_ID`, `report_serverside`, `report_clientside`, `user_ID`, `report_shortcode`) VALUES ('$reportID', '$serversideReport', NULL, '" . $_SESSION['user_account'] . "','$shortID')");

        $connection -> disconnect();
        return ["report" =>$reportID, "short" => $shortID];
    }else{
        $connection->query("UPDATE `internal_reporting_errorLog` SET `report_clientside` = '$ammendment' WHERE `internal_reporting_errorLog`.`report_ID` = '".$clientReport."'");
        $connection -> disconnect();
        return true;
    }

}
 function __returnHTTPException($adjective, $description, $errorCode=400){
     header('HTTP/1.1 '.$errorCode.' '.$adjective.': '. $description);
     header('Content-Type: application/json; charset=UTF-8');
     die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
 }
 function __post($action, $data){
     switch ($action){
         case "amendUserData":
             __logReport(null, $data['report'], $data['reportID']);
             break;
     }
 }