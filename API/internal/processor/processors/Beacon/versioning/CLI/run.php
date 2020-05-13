<?php
namespace Moycroft\API\internal\processor\EXECUTABLE;

use Moycroft\API\internal\Chron\processor\ProcessFailed;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\CLI\formatting\response\CLIDie;
use function Moycroft\CLI\formatting\response\CLIEcho;
IMPORT("API.internal.mysql.*");

function run($data=null){
    $connection = new Connect();
    $connection->connect("moycroft_beacon");
    switch ($data[1]){
        case "new":
            if (!isset($data[2], $data[3], $data[4])){
                CLIDie("You must specify Major Minor and Patch numbers.", true);
            }
            if (version_exists($data[2], $data[3], $data[4])){
                CLIDie("An entry for this version already exists.", true);
            }
            $query = "INSERT INTO `versioning_availability` (`version_major`, `version_minor`, `version_patch`, `version_available`, `version_beta`, `security_patch`) VALUES ('$data[2]', '$data[3]', '$data[4]', '1', '1', '0')";
            echo $query;
            $connection->query($query);
            break;
        case "set":
//            var_dump($data);
            if (!version_exists($data[4], $data[5], $data[6])){
                CLIDie("No entry was found for this version.", true);
            }
            switch ($data[2]){
                case "notes":
                    if ($data[3] == "internal"){
                        CLIEcho(join(" ", array_slice($data, 0, 7)));
                        CLIEcho(join(" ", $data));
                        var_dump(preg_split(join(" ", array_slice($data, 0, 7)),join(" ", $data)));
                        $notes = urlencode(preg_split(join(" ", array_slice($data, 0, 7)),join(" ", $data))[1]);
                        CLIEcho($notes);
//                        $connection->query("UPDATE `versioning_availability` SET `version_notes_internal` = '$notes' WHERE ");
                    }elseif ($data[3] == "external"){
                        $notes = urlencode(preg_split(join(" ", array_slice($data, 0, 7)),join(" ", $data)));
                    }else{
                        CLIDie("You must specify internal or external.", true);
                    }
            }
            break;
        case "unset":
            if (!version_exists($data[2], $data[3], $data[4])){
                CLIDie("No entry was found for this version.", true);
            }
            $connection->query("DELETE FROM `versioning_availability` WHERE `version_major` = '$data[2]' AND `version_minor` = '$data[3]' AND `version_patch` = '$data[4]'");
            break;
    }
}

function version_exists($major, $minor, $patch){
    $connection = new Connect();
    $connection->connect("moycroft_beacon");
    return ((sizeof($connection->query("SELECT * FROM `versioning_availability` WHERE `version_major` = '$major' AND `version_minor` = '$minor' AND `version_patch` = '$patch'", true)) !== 0));
}