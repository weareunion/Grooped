<?php

namespace Grooped\API\exceptions;

class Exception extends \Exception
{
    public $friendly = [
        "title" => "",
        "message" => ""
    ];
    // Redefine the exception so message isn't optional
    public function __construct($message, $friendly_message="", $friendly_title="", $code = 0) {
        $this->friendly["title"] = $friendly_title;
        $this->friendly['message'] = $friendly_message;
        parent::__construct($message, $code);
    }

    public function export_to_post(){
        return json_encode($this->friendly, JSON_FORCE_OBJECT);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

//    public function customFunction() {
//        echo "A custom function for this type of exception\n";
//    }
}