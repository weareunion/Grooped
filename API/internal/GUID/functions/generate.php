<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-15
 * Time: 20:32
 */
namespace Moycroft\API\internal\GUID;

use function Moycroft\API\helper\IMPORT;
IMPORT("API.internal.*");
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;


function GUID($service, $description, $temporary)
{
    $connection = new Connect();
    if (function_exists('com_create_guid') === true)
    {
        $generated =  trim(com_create_guid(), '{}');
    }else {
        $generated = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    try {
        $connection->connect();
        $temporaryCNV = 0;
        if ($temporary){
            $temporaryCNV = 1;
        }
        session_start();
        $userAssoc = 'NULL';
        if (isset($_SESSION['user_account'])){
            $userAssoc = $_SESSION['user_account'];
        }
        $connection->query("INSERT INTO `core_GUID` (`table_SEQUENCE`, `GUID`, `API_service`, `temporary`, `user_association`, `description`) VALUES (NULL, '$generated', '$service', '$temporaryCNV', '$userAssoc', '$description')");
        $connection->disconnect();
    }catch (\Exception $e){
        __error("Could not log GUID.", false);
    }
    __infoSH("Generated GUID: $generated for $service. Known as $description");
    return $generated;
}