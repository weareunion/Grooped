<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
namespace Moycroft\API\internal\Chron\processor;

$GLOBALS['INTERNAL_PROCESSOR_PROGRESS_CURRENT_SERVICE'] = "API.comms.notifications.Permissions";
$GLOBALS['INTERNAL_PROCESSOR_PROGRESS_CURRENT_ACTION'] = "permissionsChange";

function process($service, $action, $data=null, $to=null, $from=null){
    if ($service != $GLOBALS['INTERNAL_PROCESSOR_PROGRESS_CURRENT_SERVICE'] || $action != $GLOBALS['INTERNAL_PROCESSOR_PROGRESS_CURRENT_ACTION']){
        return [false, "Improper Call "];
    }
    return [false, 'False Success'];
}

