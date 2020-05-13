<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */
$connection = new \Moycroft\API\internal\mysql\Connect();
$connection->connect();
$connection->query("DELETE from employee_timetable_global where record_ID = '".$data['target_id']."'");
if (sizeof($connection->getData($connection->query("SELECT * FROM employee_timetable_global where record_ID = '".$data['target_id']."'"))) != 0){
    $success = false;
}else{
    $success = true;
}
