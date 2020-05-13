<?php
require_once dirname(__FILE__)."/";
if (defined("FLAG_MAINT_RUN") && FLAG_MAINT_RUN) {

    \Moycroft\API\CLI\response\CLIEcho("Starting to clean up databases.", null,true);

    $spacing = "\n          ";
    $totalSteps = 2;
    $currentStep = 1;

    $connection = new \Moycroft\API\internal\mysql\Connect();



    echo $spacing . "Connecting to Moycroft CORE...";
    $connection->connect();
    echo $spacing . "Starting database cleanup...";
    echo $spacing . "($currentStep/$totalSteps) Deleting temporary Global Identifiers...";
    $connection->query("DELETE FROM `core_GUID` WHERE `core_GUID`.`temporary` = 1");
    echo $spacing . "($currentStep/$totalSteps) Done.";

    $currentStep++;

    if (!isset($errorLogDepth)){
        $errorLogDepth = 50;
    }
    if (!isset($pathForData)){
        $pathForData = "../data";
    }
    if (!isset($keepErrorHistoryCount)){
        $keepErrorHistoryCount = 3;
    }

    echo $spacing . "($currentStep/$totalSteps) Creating historical error log files for rows over $errorLogDepth in sequence depth...";
    $success = false;
    $toLog = $connection->getData($connection->query("SELECT * FROM `internal_reporting_errorLog`
  WHERE `table_SEQUENCE` <= (
    SELECT `table_SEQUENCE`
    FROM (
      SELECT `table_SEQUENCE`
      FROM `internal_reporting_errorLog`
      ORDER BY `table_SEQUENCE` DESC
      LIMIT 1 OFFSET $errorLogDepth -- keep this many records
    ) foo
  )"));
    if (sizeof($toLog) == 0){
        $success = true;
        echo $spacing . "($currentStep/$totalSteps) [NOTICE] Size of error log database does not merit cleanup. Skipping process...";
    }else {
        echo $spacing . "($currentStep/$totalSteps) Deleting oldest historical log file...";
        if (!realpath(dirname(__FILE__)."/$pathForData/historical/error_logs/")) {
            echo $spacing . "[ERROR] Historical log directory (".realpath(dirname(__FILE__)."/$pathForData/historical/error_logs/").") does not exist. Please create or modify directory. Skipping process...";
        } else {
            $files = glob(dirname(__FILE__)."/$pathForData/historical/error_logs/*.*");
            array_multisort(
                array_map('filemtime', $files),
                SORT_NUMERIC,
                SORT_ASC,
                $files
            );
            

            if (sizeof($files) < $keepErrorHistoryCount) {
                echo $spacing . "($currentStep/$totalSteps) [WARNING] There are less than $keepErrorHistoryCount file(s) in the repository. Skipping process...";
            } else {
                try {
                    unlink($files[0]);
                } catch (\Exception $e) {
                    $m = $e->getMessage();
                    echo("$spacing($currentStep/$totalSteps) [ERROR] Could not delete file. Code exited with error: $m. Skipping process...");
                }
            }

            echo $spacing . "($currentStep/$totalSteps) Creating historical log file...";
                try {
                    $file = fopen("$pathForData/historical/error_logs/errorLog_" . date('D_d_M_Y@H:i:s') . ".log", "w");
                    fwrite($file, json_encode($toLog[0]));
                    fclose($file);
                    echo $spacing . "($currentStep/$totalSteps) Done.";
                    echo $spacing . "($currentStep/$totalSteps) Deleting outstanding error rows...";
                    $connection->query("DELETE FROM `internal_reporting_errorLog`
  WHERE `table_SEQUENCE` <= (
    SELECT `table_SEQUENCE`
    FROM (
      SELECT `table_SEQUENCE`
      FROM `internal_reporting_errorLog`
      ORDER BY `table_SEQUENCE` DESC
      LIMIT 1 OFFSET $errorLogDepth -- keep this many records
    ) foo
  )");
                    $success = true;
                }catch (Exception $e){
                    $m = $e->getMessage();
                    echo("$spacing($currentStep/$totalSteps) [ERROR] Could not delete file. Code exited with error: $m. Exiting process...");
                }



        }
    }
    if ($success){
        echo("$spacing($currentStep/$totalSteps) [SUCCESS] Done.");
        $success = false;
    }else{
        echo("$spacing($currentStep/$totalSteps) [FAILED] The process has failed. Continuing...");
    }
    $currentStep++;


}else{
    echo dirname(__FILE__);
}
echo "\n";
