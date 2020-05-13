<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-20
 * Time: 15:45
 */

namespace Moycroft\API\security\antispam\requests;
use function Moycroft\API\helper\IMPORT;
IMPORT("API.internal.reporting.*");

use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;

session_start();
$verbose = false;
if(isset($_SESSION['internal.API.dev.verbose']) && $_SESSION['internal.API.dev.verbose']){
    $verbose = true;
}
function requestToken($service, $action="all", $limitUses=false, $maxUses = 1){
    $token = generateRandomString(256);

    $_SESSION["API.security.antispam.requests.TOKENS"][$token] = [
        "service" => $service,
        "action" => $action,
        "uses" => 0,
        "limit" => $limitUses,
        "maxUses" => $maxUses
    ];
    __infoSH("Request token has been generated for the service $service. Token: $token" );

    return $token;
}
function validate($token, $service, $action="all", $peek=false){
    if (!isset($_SESSION["API.security.antispam.requests.TOKENS"][$token])){
        __infoSH("Validation failed: Token does not exist.", "warning");
        return false;
    }else{
        $sessionToken = $_SESSION["API.security.antispam.requests.TOKENS"][$token];

        if ($sessionToken["service"] != $service){
            __infoSH("Validation failed: Token is not usable for this service ($service)." , "warning");
            return false;
        }
        if ($sessionToken["action"] != $action){
            __infoSH("Validation failed: Token is not usable for this action.", "warning");
            return false;
        }
        if ($sessionToken["limit"]){
            if ($sessionToken["uses"] >= $sessionToken["maxUses"]){
                __infoSH("Validation failed: Token has exceeded max number of uses.");
                return false;
            }
        }
        $_SESSION["API.security.antispam.requests.TOKENS"][$token]["uses"]++;
        __infoSH("Validation succeeded.", "success");
        return true;
    }
}
function clearAllTokens(){
    $_SESSION["API.security.antispam.requests.TOKENS"] = null;
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
