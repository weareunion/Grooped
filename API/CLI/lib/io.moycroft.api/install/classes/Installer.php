<?php


namespace Moycroft\CLI\install\Installer;


use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
use function Moycroft\CLI\IO\input\yn;
use function Moycroft\CLI\registration\scripts\index\indexBin;

require_once dirname(__FILE__) . "/../../../../sbin/IO/input/input.php";
class Installer
{
    public $tempDirName = "/temp";
    private $basePlate;
    private $packageInfo = [];

    public function __construct()
    {
        $this->basePlate = realpath(str_replace(basename(__FILE__), "", __FILE__) . "../../../../");
    }

    public function install($location, $fromzip){
        try {
            $status = 0;
            define("CLI_INSTALLER_INSTALLING", true);
            CLIEcho("Preparing installer...", "info");
            if ($fromzip) {
                if (!file_exists($location)) {
                    \Moycroft\CLI\formatting\response\CLIDie("The package does not exist.");
                } else {
                    if (!explode(".", $location)[sizeof(explode(".", $location)) - 1] == ".zip") {
                        \Moycroft\CLI\formatting\response\CLIDie("Must be a .zip archive.");
                    } else {
                        $zip = new \ZipArchive;
                        if ($zip->open($location) != TRUE) {
                            CLIDie("There was a problem opening the ZIPArchive.");
                        } else {
                            $Tlocation = $location . $this->tempDirName;
                            mkdir($location . $this->tempDirName);
                            $zip->extractTo($location . $this->tempDirName);
                            $zip->close();

                            if (!$this->validate($Tlocation)) {
                                $this->packageInfo = null;
                                CLIDie("The install failed.");
                            } else {
                                $this->mount($Tlocation);
                            }
                        }
                    }
                }
            } else {
                if (!is_dir($location)) {
                    CLIDie("Directory does not exist.");
                } else {

                    if (!$this->validate($location)) {
                        $this->packageInfo = null;
                        CLIDie("The install failed.");
                    } else {
                        $this->mount($location);
                    }
                }
            }
        } catch (\Error $error) {
            CLIDie("Unknown Exception occurred: \n" . $error);
        }
    }

    private function mount($location)
    {
        $absLoc = getcwd() . $location;
        $providerDir = $this->basePlate . "/lib/" . $this->packageInfo["provider"];
        $packageDir = $this->basePlate . "/lib/" . $this->packageInfo["provider"] . "/" . $this->packageInfo["name"];
        if (!is_dir($providerDir)) {
            mkdir($providerDir);
        }
        if (is_dir($packageDir)) {
            CLIEcho("This service already exists. The version you are installing is " . $this->packageInfo['version'] . ". Reinstall?", "warning");
            $response = yn();
            if (!$response) {
                return false;
            }
        }
        mkdir($packageDir);
        $commands = scandir($location . "/.installer/bin");
        $commands[0] = "IGNORE_1";
        $commands[1] = "IGNORE_1";
        $existingCommands = scandir($this->basePlate . "/bin");
        $existingCommands[0] = "IGNORE_2";
        $existingCommands[1] = "IGNORE_2";
        $duplicates = "";
        foreach ($existingCommands as $existingCommand) {
            foreach ($commands as $command) {
                if ($command == $existingCommand) {
                    $duplicates .= explode(".", $command)[0] . ", ";
                }
            }
        }
        if ($duplicates != "") {
            CLIEcho("The following commands have already been registered: $duplicates. Installing this package will overwrite them. Continue?", "warning");
            $response = yn();
            if (!$response) {
                return false;
            }
        }
        $this->copyr($location, $packageDir);

        $this->copyr($location . "/.installer/bin", $this->basePlate . "/bin");

        require_once $this->basePlate . "/lib/io.moycroft.api/registration/scripts/index.php";
        indexBin();


        CLIEcho("Verifying installation...", "info");
        require_once $location . "/.installer/verif/finish.php";

        CLIEcho("Cleaning up...", "info");
        self::deleteDir($packageDir . "/.installer");


        if (isset($status) && $status) {
            CLIEcho("Package '" . $this->packageInfo['name'] . "' from '" . $this->packageInfo['provider'] . "' has been installed and verified", "success");
        } else {
            CLIEcho("Package '" . $this->packageInfo['name'] . "' from '" . $this->packageInfo['provider'] . "' has been installed, however the verification could not be completed.", "warning");
        }
    }

    private static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    private function validate($dir)
    {

        CLIEcho("Validating service architecture...", "info");
        if (!is_dir($dir . "/.installer")) {
            CLIDie("Installer framework is missing.", true);
            return false;
        } else {
            if (!is_dir($dir . "/.installer/bin")) {
                CLIDie("Installer framework binaries are missing.", true);
                return false;
            } else {
                $bin = scandir($dir . "/.installer/bin");
                $knownBinaries = [];
                foreach ($bin as $item) {
                    if (explode(".", $item)[1] == "cli") {
                        array_push($knownBinaries, $item);
                    }
                }
                if (sizeof($knownBinaries) == 0) {
                    CLIDie("There are no binaries.", true);
                    return false;
                }
                $installerPackage = scandir($dir . "/.installer");
                $installerPresence = [];
                foreach ($installerPackage as $item) {
                    if ($item == "package") $installerPresence["pac"] = true;
                    if ($item == "providerf") $installerPresence["prov"] = true;
                    if ($item == "registrationc") $installerPresence["reg"] = true;
                }
                if (!is_dir($dir . "/.installer/verif")) {
                    CLIDie("This package is missing finalization directory.");
                    return false;
                } else {
                    if (!file_exists($dir . "/.installer/verif/finish.php")) {
                        CLIDie("This package is missing finalization script.");
                        return false;
                    }
                }
                if (sizeof($installerPresence) != 3) {
                    CLIDie("Installer package is broken. Please contact the developer.");
                    return false;
                } else {
                    if (!file_exists($dir . "/interface/interpreter.php")) {
                        CLIDie("Interpreter is missing.", true);
                        return false;
                    } else {
                        $provider = file($dir . "/.installer/providerf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        $providerExists = false;
                        $providerIsInternal = true;
                        foreach ($provider as $item) {
                            $info = explode("=", $item);
                            if ($info[0] == "provider") {
                                $providerExists = true;
                                $this->packageInfo["provider"] = $info[1];
                                if ($info[1] != "io.moycroft.api") {
                                    CLIEcho("This service has not been developed by Moycroft, and may damage your system or expose it to vulnerabilities. Moycroft Systems does not cover damage caused by third-party scripts. Continue?", "warning");
                                    $response = yn();
                                    if (!$response) {
                                        $providerIsInternal = false;
                                    } else {
                                        $providerIsInternal = true;
                                    }
                                }
                            }
                        }
                        if (!$providerExists || !$providerIsInternal) {
                            CLIDie("This install package is unsigned.", true);
                            return false;
                        } else {
                            $pck = json_decode(file_get_contents($dir . "/.installer/package"), JSON_OBJECT_AS_ARRAY);

                            if ($pck == null) {
                                CLIDie("Invalid JSON in package");
                                return false;
                            } else {
                                if (!isset($pck['version'][0], $pck['name'][0])) {
                                    CLIDie("Invalid package");

                                    return false;
                                } else {
                                    $this->packageInfo["name"] = $pck["name"][0];
                                    $this->packageInfo["version"] = $pck["version"][0];
                                    $commands = json_decode(file_get_contents($dir . "/.installer/registrationc"), JSON_OBJECT_AS_ARRAY);
                                    $this->packageInfo["commands"] = [];
                                    if ($commands == null) {
                                        CLIDie("Invalid JSON in registration");
                                        return false;
                                    } else {
                                        $hasPrimary = false;
                                        foreach ($commands as $command) {
                                            if ($command['alias'] == false) {
                                                $hasPrimary = true;
                                            }
                                            array_push($this->packageInfo["commands"], $command);
                                        }
                                        if (!$hasPrimary) {
                                            CLIDie("No primary command exists");
                                            return false;
                                        }
                                    }
                                }
                            }
                            $this->packageInfo["commands"] = $this->packageInfo["commands"][0];
                            sleep(.5);
                            CLIEcho("Installing...", "info");
                            return true;
                        }
                        }
                    }
                }
            }

    }

    private function copyr($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyr($src . '/' . $file, $dst . '/' . $file);
                } else {
                    CLIEcho("Installing " . $src . '/' . $file);
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}