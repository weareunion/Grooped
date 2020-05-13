<?php


namespace Moycroft\CLI\registration;
require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
require_once dirname(__FILE__) . "/../../../../sbin/IO/input/input.php";
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
use function Moycroft\CLI\IO\input\yn;
use function Moycroft\CLI\registration\scripts\index\indexBin;


class Registration
{
    private $binaryLocation;
    private $registrationRoster;
    public function __construct()
    {
        $this->binaryLocation = dirname(__FILE__) . "/../../../../bin";
        $this->registrationRoster = $this->binaryLocation . "/../var/bin/registration/cliReg.json";
    }
    public function lookup($service){

        $this->checkFiles();
        $roster = json_decode(file_get_contents($this->registrationRoster), JSON_OBJECT_AS_ARRAY);
        if ($roster == null){
            CLIDie("Roster is not readable. Please reindex.", true);
        }
        if (!isset($roster[$service])){
            return false;
        }else{
            return $roster[$service];
        }
    }
    public function remove($service){
        if (!$this->lookup($service)){
            CLIDie("This service does not exist and therefore cannot be removed.");
        }else{
            CLIEcho("Are you sure you want to remove '$service'? This action cannot be undone.");
            try {
                $r = yn();
            }catch (\Error $e){
                CLIDie("An Unexpected Error has occured: $e");
            }
            if (!$r){
                CLIEcho("Cancelled.");
            }else{
                CLIEcho("Removing $service...");
                if (!unlink($this->binaryLocation . "/$service.cli")){
                    CLIDie("Removal failed.");
                }else{
                    CLIEcho("Reindexing...");
                    $s = true;
                    try {
                        require_once dirname(__FILE__) . "/../scripts/index.php";
                        indexBin();
                    }catch (\Error $e){
                        $s = false;
                        CLIDie("Removal failed.");
                    }
                    if ($s){
                        CLIEcho("Command Removed", "success");
                    }
                }


            }


        }
    }
    public function checkFiles(){
        if (!file_exists($this->registrationRoster)){
            CLIDie("Could not find roster.", true);
        }
    }
}