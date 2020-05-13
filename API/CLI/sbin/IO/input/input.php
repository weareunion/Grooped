<?php
namespace Moycroft\CLI\IO\input;
function input(){
    printf("\n");
    $resSTDIN = fopen("php://stdin", "r");
    echo("             ->> ");
    $strChar = fgets($resSTDIN);
    fclose($resSTDIN);
    return str_replace(array("\n", "\r"), '', $strChar);
}
function yn(){
    $isNotValid = true;
    while ($isNotValid){
        $inp = input();
        $inp = strtoupper($inp);
        if ($inp == "Y" || $inp == "YES"){
            return true;
        }
        if ($inp == "N" || $inp == "NO"){

            return false;
        }
        echo "Input not valid. 'Yes or Y' and 'N or No' in any case are valid.";
    }
}
