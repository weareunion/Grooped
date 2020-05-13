<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
namespace Moycroft\API\internal\Chron\processor;

$service = "Moycroft.System.Testbench";
$action = "void";

function process($service, $action, $data=null, $to=null, $from=null){
    return [false, 'Test Fail'];
}

