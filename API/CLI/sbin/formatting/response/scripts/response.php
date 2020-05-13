<?php
namespace Moycroft\CLI\formatting\response;
$return_as_WE = [];
if (defined("IN_WEB_INTERFACE")) {
    $return_as_WE = [
        "channel" => WEB_CHANNEL_ID
    ];
}
function CLIDie($message, $die=true){
    CLIEcho($message, "error");
    if (!defined("IN_WEB_INTERFACE")) {
        die();
    }else{
        throw new \Exception("Terminating");
    }
}
function CLIEcho($message, $type=null, $addTab=false,$newline=true){
    global $prefix, $return_as_WE;
    $newlineInsert = $newline ? "\n" : "";
    $postfix = "";
    $prefixInt = "";
    $suffix = "\e[0m";
    if ($type == "success"){
        $postfix = "\e[102m ✔ ▶ SUCCESS \e[0m\e[92m ";
    }
    if ($type == "warning"){
        $postfix = "\e[103m ⚠ ▶ WARNING \e[25m\e[0m\e[93m ";
    }
    if ($type == "info"){
        $postfix = "\e[46m [ℹ] ▶ NOTICE \e[0m\e[96m ";
    }
    if ($type == "error"){
        $postfix = "\e[1m\e[101m ⚠ ▶ ERROR \e[25m\e[0m\e[1m\e[91m ";
    }
    if ($type == null){
        $prefixInt = "             - " . $prefix;
    }
    if ($addTab){
        $prefixInt .= "    " . $prefix;
    }
    if (defined("IN_WEB_INTERFACE")){
        if (!isset($_SESSION['API.CLI.web.sessions'][WEB_CHANNEL_ID]['callback'])){
            $_SESSION['API.CLI.web.sessions'][WEB_CHANNEL_ID]['callback'] = [];
        }
        array_push($_SESSION['API.CLI.web.sessions'][WEB_CHANNEL_ID]['callback'], [
            "type"=> urlencode($type),
            "message" => urlencode($message),
            "message_format" => urlencode("$newlineInsert$prefixInt $postfix $message $suffix")
        ]);
    }else {
        printf("$newlineInsert$prefixInt $postfix $message $suffix");
    }
}
