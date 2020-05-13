<?php
namespace Moycroft\API\internal\processor\EXECUTABLE;

use Moycroft\API\internal\Chron\processor\ProcessFailed;

function run($data=null){
    if (!isset($data['number'])){
        throw new ProcessFailed("Error");
    }
    echo "Result: ". ($data['number']*50);
}