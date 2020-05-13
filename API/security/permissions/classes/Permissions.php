<?php


namespace Moycroft\API\security\permissions\Permissions;
use Moycroft\API\accounts\Account\Account;
use Moycroft\API\comms\notifications\Notification\Notification;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\reporting\report\__crash;

IMPORT("API.internal.*");
IMPORT("API.comms.notifications.*");

class Permissions
{
    static public $possibleActions = [
        "API.employee.timetables" => [
            "delete" => "Delete a timetable report."
        ]
    ];
    static function process($referenceID){
        $data = self::getData($referenceID);
        if (!$data){
            return false;
        }
         $userID = null;
                 if (!$userID){
                     session_start();
                     if (!isset($_SESSION['user_account'])){
                         __crash("Session must be set and logged in user must be present");
                         return false;
                     }else{
                         $userID = $_SESSION['user_account'];
                     }
                 }
        if ($data['recipient_id'] != $userID){
            return false;
        }
        $_SESSION['API.SECURITY.PERMISSION.PASSTHRU'] = $data;
        header("Location: ../../permissions/process");
    }
    static function deny($referenceID){
        $data = self::getData($referenceID);
        if (!$data){
            __crash("Does not exist");
        }
         $userID = null;
                 if (!$userID){
                     session_start();
                     if (!isset($_SESSION['user_account'])){
                         __crash("Session must be set and logged in user must be present");
                         return false;
                     }else{
                         $userID = $_SESSION['user_account'];
                     }
                 }
        if ($data["recipient_id"] != $userID){
            __crash("User is not the requester.");
            return false;
        }

        $connection = new Connect();
        $connection->connect();
        $connection->query("UPDATE security_permissions_general_lookup SET status=-1 where reference_id='$referenceID'");

        $notif = new Notification();
        $recName = (new Account())->getFirstName($data['recipient_id']) . " " . (new Account())->getLastName($data['recipient_id']);
        $recNameFirst = (new Account())->getFirstName($data['recipient_id']);
        $notif->setMessage("Your request was denied", null, "Your request to $recName for '" . $data['description'] . "' has been denied. You may contact $recNameFirst to resolve or dispute this action.");
        $notif->setRecipient($data['requester_id']);
        $notif->setServiceLocator("API.security.permissions");
        $notif->setDelivery("122", false,true, true, false);
        $notif->queue();
    }
    static function approve($referenceID){
        $data = self::getData($referenceID);
        if (!$data){
            __crash("Does not exist");
        }
        $userID = null;
        if (!$userID){
            session_start();
            if (!isset($_SESSION['user_account'])){
                __crash("Session must be set and logged in user must be present");
                return false;
            }else{
                $userID = $_SESSION['user_account'];
            }
        }
        if ($data["recipient_id"] != $userID){
            __crash("User is not the requester.");
            return false;
        }
        $success = false;
        require_once __DIR__ . "/../processors/".$data["service"]."/".$data["action"].".php";
        $notif = new Notification();
        $end = $success ? " and fulfilled." : ", however the request could not be completed. You may contact support with the following ID to help us resolve this issue. ($referenceID)";
        $recName = (new Account())->getFirstName($data['recipient_id']) . " " . (new Account())->getLastName($data['recipient_id']);
        $recNameFirst = (new Account())->getFirstName($data['recipient_id']);
        $notif->setMessage("Your request was approved", null, "Your request to $recName for '" . $data['description'] . "' has been approved$end");
        $notif->setRecipient($data['requester_id']);
        $notif->setServiceLocator("API.security.permissions");
        $notif->setDelivery("002", false,true, false, false);
        $notif->queue();
    }
    static function request($recipient, $service, $action, $target, array $data = null){
        if (!isset(self::$possibleActions[$service][$action])){
            __crash("Not a possible action");
        }
        if (!(new Account())->accountExists($recipient)){
            __crash("Recipient does not exist.");
        }
        $description = self::$possibleActions[$service][$action];
        $acc = new Account();
        $connection = new Connect();
        $connection->connect();
         $userID = null;
                 if (!$userID){
                     session_start();
                     if (!isset($_SESSION['user_account'])){
                         __crash("Session must be set and logged in user must be present");
                         return false;
                     }else{
                         $userID = $_SESSION['user_account'];
                     }
                 }
                 if ($data != null) {
                     $data = urlencode(json_encode($data));
                 }
                 $refID = GUID("API.security.permissions", "Permission REFID", false);
        $connection->query("INSERT INTO `security_permissions_general_lookup` (`reference_id`, `requester_id`, `recipient_id`, `service`, `action`, `target_id`, `data`, `set`, `status`, `description`) VALUES ('$refID', '$userID', '$recipient', '$service', '$action', '$target', '$data', NOW(), 0, '$description')");
        $notif = new \Moycroft\API\comms\notifications\Notification\Notification();
        $name = $acc->getFirstName($_SESSION['user_account']) . " " . $acc->getLastName($_SESSION['user_account']);
        $notif->setMessage("Someone requests your Permission", "You have the option to approve or deny this request", "$name has requested to run an action that requires your permission.");
        $notif->setLink(\Moycroft\API\comms\notifications\Notification\Notification::createHyperlink("API.security.permissions", "run", $refID, [
            "test" => "testData"
        ], true), "Respond to Request");
        $notif->setRecipient($recipient);
        $notif->setServiceLocator("API.security.permissions");
        $notif->setDelivery("122", false,true, true, false);
        $notif->queue();


        $notif = new \Moycroft\API\comms\notifications\Notification\Notification();
        $name = $acc->getFirstName($recipient) . " " . $acc->getLastName($recipient);
        $notif->setMessage("Request has been sent", null, "You have sent a request to $name. We will let you know when they respond.");
        $notif->setRecipient($_SESSION['user_account']);
        $notif->setServiceLocator("API.security.permissions");
        $notif->setDelivery("002", false,true, false, false);
        $notif->queue();
    }
    static function getData($referenceID){
        $connection = new Connect();
        $connection->connect();
        $retval = $connection->getData($connection->query("SELECT * FROM security_permissions_general_lookup WHERE reference_id='$referenceID'"));
            if ($retval == null || sizeof($retval) == 0){
                return false;
            }
            return $retval[0];
    }
}