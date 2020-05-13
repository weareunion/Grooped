<?php
namespace Moycroft\CLI\registration\scripts\index;
require_once dirname(__FILE__) . "/../../../../sbin/formatting/response/scripts/response.php";
function indexBin()
{
    \Moycroft\CLI\formatting\response\CLIEcho("Indexing binaries...");
    $binaryLocation = dirname(__FILE__) . "/../../../../bin";
    $registrationRoster = $binaryLocation . "/../var/bin/registration/cliReg.json";
    $totalBinaries = 0;
    if (!is_dir($binaryLocation)) {
        \Moycroft\CLI\formatting\response\CLIDie("Binaries could not be accessed. Does the user have permission?", true);
    } else {
        $binaries = $files = array_diff(scandir($binaryLocation), array('.', '..', '.cli'));
    }
    $export = [];
    foreach ($binaries as $binary) {
        $binaryName = explode(".", $binary)[0];
        $contents = file($binaryLocation . "/" . $binary, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $inspect = validateCLI($contents);
        if (!$inspect) {
            \Moycroft\CLI\formatting\response\CLIEcho("Could not read registration for $binary. This command will not be registered.", "warning");
        } else {
            $export[$binaryName] = $inspect;
            $totalBinaries++;
        }
    }
    if (!file_exists($registrationRoster)) {
        \Moycroft\CLI\formatting\response\CLIDie("Registration roster does not exist.", true);
    } else {
        file_put_contents($registrationRoster, json_encode($export, JSON_PRETTY_PRINT));
        \Moycroft\CLI\formatting\response\CLIEcho("$totalBinaries binaries have been indexed.", "success");
    }
}
function validateCLI($in){
    $out = [];
    if (sizeof($in) < 4){
        return false;
    }
    if ($in[0] != "?register"){
        return false;
    }
    $alias = null;
    $installer = null;
    $provider = null;
    $service = null;
    foreach ($in as $item){
        $expl = explode("=", $item);
        if (!(sizeof($expl)==1)){
            if ($expl[0] == "alias") {
                if($expl[1] == "true") $alias = true;
                if($expl[1] == "false") $alias = false;
            };
            if ($expl[0] == "installer") $installer = $expl[1];
            if ($expl[0] == "provider") $provider = $expl[1];
            if ($expl[0] == "service") $service = $expl[1];
        }
    }
    if ($alias === null || $installer == null || $provider == null || $service == null){
        return false;
    }
    return [ "alias" => $alias,
        "provider" => $provider,
        "service" => $service,
        "installer" => $installer];
}