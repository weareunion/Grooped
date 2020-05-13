<?php
namespace Moycroft\API\internal\processor\EXECUTABLE;

use Moycroft\API\internal\Chron\processor\ProcessFailed;
use function Moycroft\CLI\formatting\response\CLIEcho;

function run($data=null){
    if (!isset($data[1], $data[2])){
        throw new ProcessFailed("There was no number specified! - Put a number in the 2nd field to have it multiplied by the one in the 3rd!");
    }
    CLIEcho( "Result: ". ($data[1]*$data[2]));
}