<?php

 require_once dirname(__FILE__) . "/../../../classes/Maintenance.php";
$maint = new \Moycroft\API\internal\maintenance\Maintenance();
$maint ->run();
