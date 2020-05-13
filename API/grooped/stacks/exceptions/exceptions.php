<?php
// EXCEPTIONS

use Grooped\API\exceptions\Exception;

class InvalidStack extends Exception{
    public function __construct($message, $friendly_message = "", $friendly_title = "", $code = 0)
    {
        parent::__construct($message, $friendly_message, $friendly_title, $code);
    }
}

class StackDoesNotExist extends Exception{
    public function __construct($message, $friendly_message = "", $friendly_title = "", $code = 0)
    {
        parent::__construct($message, $friendly_message, $friendly_title, $code);
    }
}

class UnregisteredAction extends Exception{
    public function __construct($message, $friendly_message = "", $friendly_title = "", $code = 0)
    {
        parent::__construct($message, $friendly_message, $friendly_title, $code);
    }
}

class UnknownError extends Exception{
    public function __construct($message, $friendly_message = "", $friendly_title = "", $code = 0)
    {
        parent::__construct($message, $friendly_message, $friendly_title, $code);
    }
}
