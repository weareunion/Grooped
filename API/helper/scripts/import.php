<?php
/**
 * @name API/helper/scripts/import.php
 * @description: Creates IMPORT() function
 * @package API.helper.import
 * @api General Backend API
 * @api Admin API
 * @copyright Cor3 Design, LLC
 * @author Karl Schmidt
 */
namespace Moycroft\API\helper;

    $require_script = true;

    class API_background_IMPORT{
        private $import;
        private $addresses;
        public function __construct()
        {
        }
        public function set($locator){
            if (gettype($locator) != "string"){
                $this->__crash("Item is not STRING");
            }
            $this->import = $locator;
        }
        public function validate($returnAsInt=false){
            $this->__info("Validating Service Address.");
            $this->addresses = explode(".", $this->import);
                if (
                    (($this->addresses[0] . $this->addresses[1] . $this->addresses[2]) != "iomoycroftAPI")
                    && ($this->addresses[0] != "API")
                ){

                    if ($this->addresses[0] != "io" || $this->addresses[1] != "moycroft"){
                        $this->__crash(": External script imports are not yet supported.");
                    }else{
                        $this->__crash(": Invalid Locator String");
                    }

             }else{
                    $this->__info("Basic internal API check passed.");
//                    $previousCWD = getcwd();
                    $location = GLOBAL_CONFIG["api"]["location"] . "/";
                    foreach ($this->addresses as $item){
                        if ($item != "io" && $item != "moycroft" && $item != "API" && $item != $this->addresses[sizeof($this->addresses)-1]) {
                            $location .= $item . "/";
                        }
                    }

                    $this->__info("Created assumed file location. Running tests on this now.");
                    if (!file_exists($location)){
                        $this->__crash("Package does not exist");
                    }else{
                        $this->__info("Files exist. Continuing tests.");
                        if (!file_exists($location . "manifest.json") && "*" != $this->addresses[sizeof($this->addresses)-1] ){
                            $this->__crash("Manifest does not exist ");
                        }else{
                            $this->__info("Manifests exists or * import used.");
                            if ("*" == $this->addresses[sizeof($this->addresses)-1]) {
                                $this->__info("* import used. Beginning recursive scan.");
//                           echo var_dump( $this->getSubDirectories($location));
                                $known = $this->getSubDirectories($location);
                                $workable = [];

                                foreach ($known as $dir) {
                                    if (file_exists($dir . "/manifest.json")) {
                                        array_push($workable, $dir);
                                    }
                                }
                                $this->__info("Scan completed. " . sizeof($workable) . " workable manifests found. Running through  files.", "success");
                                foreach ($workable as $dir){
                                    $this->process($this->validateManifest($dir), $dir, null);
                                }
                            }else{
//                                echo "triggered";
                                $this->process($this->validateManifest($location), $location, $this->addresses[sizeof($this->addresses)-1]);
                            }
                        }
                    }
              }
        }
        private function loadDepenancies(){

        }
        private function process($manifest, $dir, $subpackage=null){
            $classes =[];
            $actions = [];
            $other = [];
            if (!isset($dir)){
                $this->__crash("Location not set");
            }
            $dir .= "/";
            foreach ($manifest["imports"] as $toSort){
                if ($subpackage==null || (isset( $manifest["imports"][$subpackage]) && $toSort == $manifest["imports"][$subpackage]))
                foreach ($toSort as $index => $item) {
                    if ($item == "class"){
                        array_push($classes, $index);
                    }elseif ($item == "action"){
                        array_push($actions, $index);
                    }else{
                        if ($index != null) {
                            array_push($other, $index);
                        }
                    }
                }
            }
            foreach ($classes as $class){
                $this->runImport($dir . $class);
            }
            foreach ($actions as $action){
                $this->runImport($dir . $action);
            }
            foreach ($other as $item){
                $this->runImport($dir. $item);
            }
        }
        private function validateManifest($dir){
            $this->__info("Starting scan on Manifest @ " . $dir . "");
            $manifest = json_decode( file_get_contents($dir . "/manifest.json"), true);
            if ($manifest === null){
                $this->__crash("Invalid Manifest File");
            }else{
                $this->__info("&nbsp&nbsp&nbsp&nbsp&nbsp Manifest @ " . $dir . ": is valid JSON convention.");
                if (!isset($manifest["service"])){
                    $this->__crash("No service ID.");
                }
                $this->__info("&nbsp&nbsp&nbsp&nbsp&nbsp Manifest @ " . $dir . ": has a valid service ID.");
                if (isset($manifest["required_imports"]) && $manifest["required_imports"] == true && !isset($manifest["imports"]["_required"])){
                    $this->__crash("Required imports list not found.");
                }
                $this->__info("&nbsp&nbsp&nbsp&nbsp&nbsp Manifest @ " . $dir . ": has been checked for required imports");
                if (!isset($manifest["imports"]) || sizeof($manifest["imports"]) == 0){
                    $this->__crash("No Imports Specified. Skipping", true);
                    $this->__info("&nbsp&nbsp&nbsp&nbsp&nbsp Manifest @ " . $dir . ": has no imports. Assumed to be a developmental package.");
                }else{
                    $this->__info("&nbsp&nbsp&nbsp&nbsp&nbsp Manifest @ " . $dir . ": has imports.");
                }
                $this->__info("Finished validating Manifest @ " . $dir . " successfully.", "success");
                return $manifest;
            }
            return;
        }

        private function runImport($dir){
            if (file_exists($dir)){
                $this->__info("Importing script from: " . $dir);
                include_once $dir;
            }else{
                $this->__crash("File '" . $dir. "' does not exist");
            }
        }
        private function getSubDirectories($dir)
        {
            $subDir = array();
            // Get and add directories of $dir
            $directories = array_filter(glob($dir), 'is_dir');
            $subDir = array_merge($subDir, $directories);
            // Foreach directory, recursively get and add sub directories
            foreach ($directories as $directory) $subDir = array_merge($subDir, $this->getSubDirectories($directory.'/*'));
            // Return list of sub directories
            return $subDir;
        }
        function __crash($title, $warning=false){

            global $require_script, $verbose;
            if ($require_script && !$verbose && !$warning){
                throw new \Error("<span style=\"color:red\">IMPORT (Error): " . $title. "</span><br>");
            }
            if (!$warning) {
                $color = "orangered";
            }else {
                $color = "orange";
            }
                $small = "small";
                if ($verbose){
                    echo "<span style=\"color:$color\"><small>[<strong> >> ! << </strong>] -> <strong>" ."[ERROR from " . __CLASS__ . " @ " . __DIR__ . "] </strong></small> " . $title . "</span><br>";
                }else{
                    throw new \Error($title);
                }

            }

        static $logCount = 0;
        function __info($message, $type="info"){
            global $verbose;
            if ($verbose) {
                if (self::$logCount === 0) {
                    echo "<style>body {background-color: #232525;}</style>";
                    echo "<small><strong><span style=\"color:deeppink\">" . "[<strong> >> $ << </strong>] -> [INFO from " . __CLASS__ . " @ " . __DIR__ . "] </strong></small><span style=\"color:deeppink\">" . "Launching script" . "</span></span><br><hr style=\"border-color:white; height: 0px;border: 0\">";
                }
                self::$logCount++;
                $color = "deepskyblue";
                $small = "small";
                $messageColor = "darkgray";
                if ($type == "success") {
                    $color = "mediumspringgreen";
                    $messageColor = "mediumspringgreen";
//            $small = "italic";
                }
                if ($verbose) {
                    echo "<$small><strong><span style=\"color:$color\">" . "[<strong> >> " . self::$logCount . " << </strong>] -> [INFO from " . __CLASS__ . " @ " . __DIR__ . "] </strong></$small><span style=\"color:$messageColor\">" . $message . "</span></span><br><hr style=\"border-color:white; height: 0px;border: 0\">";
                }
            }
        }
    }

    function IMPORT($import, $require=true){
        global $require_script;
        $require_script = $require;
                $importObject = new API_background_IMPORT();
                if (gettype($import) == "string"){
                    $importObject->set($import);
                    $importObject->validate();
                }elseif (gettype($import) == "array"){

                }else{
                    $this->__crash(": Requested import parameter not of type STRING or ARRAY.");
                }

    }




