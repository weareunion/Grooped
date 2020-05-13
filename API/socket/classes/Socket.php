<?php

namespace Moycroft\API\socket\Socket;
use Moycroft\API\comms\notifications\Notification\Notification;
use Moycroft\API\comms\notifications\Postal\Postal;
use function Moycroft\API\helper\IMPORT;

class Socket
{
    static function compile(){
        return (new Socket())->startCompile();
    }

    static function encode(array $outgoing){
        if(sizeof($outgoing) != 0) return json_encode($outgoing, JSON_OBJECT_AS_ARRAY);
    }
    
    public $compiledReturn = [];

    function startCompile(){
        $notifications =  $this->getNotifications();
        if ($notifications != []) $this->compiledReturn["notifications"] = $notifications;
        return $this->cleanCompile($this->returnCompile());
    }
    
    private function getNotifications(){
        $notifications = [];
        IMPORT("API.comms.notifications.*");
        $incoming = Postal::requestSocketMessages();
        return $incoming;
    }
    
    private function returnCompile(){
        return $this->compiledReturn;
    }

    private function cleanCompile($data){
        return $data;
    }
    
}

function __post($action, $incoming)
{

}