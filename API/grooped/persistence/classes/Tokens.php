<?php


namespace Grooped\API\persistence\Tokens;


use Moycroft\API\internal\mysql\Connect;

\Moycroft\API\helper\IMPORT("API.internal.GUID.*");
\Moycroft\API\helper\IMPORT("API.internal.mysql.*");
class Tokens
{
    static function auto(){
        if (self::preventRegistration()){
            return false;
        }
        if (!self::isRegistered()){
            self::register();
        }else {
            if (!self::load()) {
                self::register();
            }
        }
        self::ping();
    }

    static function preventRegistration(){
        return (isset($_COOKIE['prevent_identification']) && $_COOKIE['prevent_identification'] == true);
    }

    static function getCurrentPersistenceToken(){
        if (self::preventRegistration()){
            return false;
        }
        return $_COOKIE['persistence_token'];
    }

    static function setName($name){
        if (!self::isRegistered()){
            return false;
        }
        self::DBConnect()->query("UPDATE `moycroft_grooped`.PERSISTENCE_TOKENS_COREDATA SET LAST_NAME_USED='$name' WHERE TOKEN_ID = '".$_COOKIE['persistence_token']."';");
    }

    static function ping(){
        self::DBConnect()->query("UPDATE `moycroft_grooped`.PERSISTENCE_TOKENS_COREDATA SET `LAST_ACTIVE`=NOW() WHERE TOKEN_ID = '".$_COOKIE['persistence_token']."';");
    }

    static function togglePreventRegistration(){
        if (self::preventRegistration()){
            $_COOKIE['prevent_identification'] = true;
        }else{
            $_COOKIE['prevent_identification'] = false;
        }
    }

    static function isRegistered(){
        return isset($_COOKIE['persistence_token']) && sizeof(self::DBConnect()->query("SELECT * FROM moycroft_grooped.PERSISTENCE_TOKENS_COREDATA WHERE TOKEN_ID = '".$_COOKIE['persistence_token']."';", true)) != 0;
    }

    static function getName($token_id=null){
        if ($token_id == null){
            if (isset($_COOKIE['persistence_token'])) {
                $token_id = $_COOKIE['persistence_token'];
                if (session_status() != 2) session_start();
                if (isset($_SESSION['persistence']['TOKEN_ID'])
                    && $_SESSION['persistence']['TOKEN_ID'] == $_COOKIE['persistence_token']
                    && isset($_SESSION['persistence']['LAST_NAME_USED'])){
                    return $_SESSION['persistence']['LAST_NAME_USED'];
                }
            }else{
                return false;
            }
        }
        $res = self::DBConnect()->query("SELECT LAST_NAME_USED FROM moycroft_grooped.PERSISTENCE_TOKENS_COREDATA WHERE TOKEN_ID = '$token_id';", true);
        if (sizeof($res) == 0)
        {
            return false;
        }
        return $res[0]['LAST_NAME_USED'];
    }
    static function getAccountId($token_id=null){
        if ($token_id == null){
            if (isset($_COOKIE['persistence_token'])) {
                $token_id = $_COOKIE['persistence_token'];
                if (session_status() != 2) session_start();
                if (isset($_SESSION['persistence']['TOKEN_ID'])
                    && $_SESSION['persistence']['TOKEN_ID'] == $_COOKIE['persistence_token']
                    && isset($_SESSION['persistence']['LINKED_ACCOUNT_ID'])){
                    return $_SESSION['persistence']['LINKED_ACCOUNT_ID'];
                }
            }else{
                return false;
            }
        }
        $res = self::DBConnect()->query("SELECT LINKED_ACCOUNT_ID FROM moycroft_grooped.PERSISTENCE_TOKENS_COREDATA WHERE TOKEN_ID = '$token_id';", true);
        if (sizeof($res) == 0)
        {
            return false;
        }
        return $res[0]['LINKED_ACCOUNT_ID'];
    }

    static function register(){
        $token_id = \Moycroft\API\internal\GUID\GUID("Grooped.persistence", "A token to keep recent actions on user account", false);
        if (self::DBConnect()->query("INSERT INTO `moycroft_grooped`.PERSISTENCE_TOKENS_COREDATA (`TOKEN_ID`, `LAST_ACTIVE`) VALUES ('$token_id', NOW());")){
            setcookie("persistence_token", $token_id, time() + (60*60*24*365));
            self::load($token_id);
            return true;
        }
        return false;
    }

    static function load($token=null){
        if ($token == null){
            $token = $_COOKIE['persistence_token'];
        }
        $data = self::DBConnect()->query("SELECT * FROM moycroft_grooped.PERSISTENCE_TOKENS_COREDATA WHERE TOKEN_ID = '".$token."';", true);
        if (sizeof($data) == 0){
            return false;
        }
        if (session_status() != 2) session_start();
        $_SESSION['persistence'] = $data;
        var_dump($data);
        return true;
    }

    static function DBConnect(){
        $connection = new Connect();
        $connection->connect('moycroft_grooped');
        return $connection;
    }
}