<?php
/**
 * @name API/config.php
 * @description: This CONFIG file sets important env variables, imports scripts, runs generally important code (like sessioning), and allows configuration by
 *              defining constants before the script is imported.
 * @package API
 * @api General Backend API
 * @api Admin API
 * @copyright Cor3 Design, LLC
 * @author Karl Schmidt
 */
namespace Moycroft\API\config;

use Moycroft\API\accounts\Account\Account;
use function Composer\Autoload\includeFile;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\reporting\report\__error;

/**
 * Configuration is ordered by service in the same fashion as package naming without the domain name.
 * You can set up a script's preferences by creating an internally defined variable for more easy access.
 * @example Notification script setup could look something like
 *          define("CONFIG", GLOBAL_CONFIG["api"]["comms"]["notifications"]["create"]). Now access preferences by CONFIG["allow_post"].
 */

/**
 * %%%Clean and prepare script-specific preferences from constants
 */
$contants =[
    'CONFIG_RESETSESSION' => false,
    'CONFIG_PREVENTSESSION' => false,
    'CONFIG_SESSIONID' => null,
    'CONFIG_PREVENTIMPORTS' => true,
    'CONFIG_PREVENTIMPORTS_SECURITY' => true,
    'CONFIG_PREVENTIMPORTS_PERFORMANCE' => false,
    'CONFIG_RUNASVERBOSE' => false
];
helper_cleanEnvironmentConstants($contants);
/**
 * ---------------------------CONFIG----------------------------
 * @section Configuration
 * @description Configures core preference array
 */

/**
 * This configuration array should be loaded into every script, even if there are no preferences
 * set here yet, future implementation might need it.
 *
 * 1) If a script needs a certain preference, the CONFIG_CUSTOM constant can be defined with an array,
 * with the values changed that need to be changed. Other parts of the array may be omitted. Hierarchy must be preserved.
 */

eval(file_get_contents(__DIR__ . "/etc/config/api.conf"));

helper_considerCustomConfigArray();
define("GLOBAL_CONFIG", $config);


/**
 * ---------------------------GIC----------------------------
 * @section Generally Important Code
 * @actions Sessioning
*/

/**
 * Starts a session or continues an existing one.
 *
 * 1) Can be overwritten by putenv("CONFIG_PREVENTSESSION", true)
 * 2) Custom IDs can be set using putenv("CONFIG_SESSIONID", {{sessionid}})
 * 3) Sessions can be reset using putenv("CONFIG_RESETSESSION", true)
 */
if (CONFIG_RESETSESSION){
    session_destroy();
}
if (!CONFIG_PREVENTSESSION){
    if (CONFIG_SESSIONID !== null){
        session_id(CONFIG_SESSIONID);
    }
    ini_set('session.gc_maxlifetime', 36000);
    session_set_cookie_params(36000);

    session_start();
    if (!isset($_SESSION['dev.log'])) {
        $_SESSION['dev.log'] = "";
    }
}

/**
 * ---------------------------IMPORT---------------------------
 * @section Loads IMPORT function script and triggers its autoload
 */

require_once "helper/autoload.php";

/**
 * ---------------------------SET ERROR REPORTING---------------------------
 * @section Loads IMPORT function script and triggers its autoload
 */
    IMPORT("API.internal.reporting.*");

    function t_errorHandle($errno, $errstr, $errfile, $errline){

        if (!strpos(strtoupper($errstr), "WARNING") && !strpos(strtoupper($errstr), "CONSTANT")) {


            try {
                global $errno, $errstr, $errfile, $errline;
                if ($errstr != null)  __error("$errno: $errstr @ $errfile : $errline", true);
            } catch (\Exception $e) {

            }
        }
    }
    error_reporting(E_ERROR | E_PARSE );
    set_error_handler("Moycroft\API\config\\t_errorHandle");
/**
 * ---------------------------SCRIPTS---------------------------
 * @section Importing useful scripts
 *
 * All imports can be stopped by using putenv("CONFIG_PREVENTIMPORTS", true)
 */

if (!CONFIG_PREVENTIMPORTS) {
    /**
     * Security Package
     * @package API.security
     */
    if (!CONFIG_PREVENTIMPORTS_SECURITY) {
        IMPORT("io.moycroft.API.security.auth.gatekeeper.*");
    }
    if (!CONFIG_PREVENTIMPORTS_PERFORMANCE){
        IMPORT("API.performance.*");
    }

}
/**
 * ---------------------------VERBOSE---------------------------
 * @section Adding a universal verbose flag
 *
 * All scripts should react verbosely when this flag is set to true.
 */
if (!isset( $_SESSION['internal.API.dev.verbose'])){
    $_SESSION['internal.API.dev.verbose'] = false;
}

/**
 * ------------------CONFIG HELPER FUNCTIONS-----------------
 * @section Used by this script to prepare enviroment and process important
 */
/**
 * Function helper_cleanEnvironmentConstants
 * @param $arrayOfConstants - array of constants that are expected by the code, to define and set to default value
 */
function helper_cleanEnvironmentConstants($arrayOfConstants){
    foreach ($arrayOfConstants as $index => $default) {
        if (!defined($index)){
            define($index, $default);
        }
    }
}
//
function helper_considerCustomConfigArray(){
    global $config;
    if (defined("CONFIG_CUSTOM") && gettype(CONFIG_CUSTOM) == "array"){
        $config = array_replace_recursive($config, CONFIG_CUSTOM);
    }
}

/**
 * Config Set Marker
 */


$configPresent = true;


/**
 * ------------------LOCKDOWN-----------------
 * @section Used by the system to fulfill lockdown
 */

if (file_get_contents(str_replace(basename(__FILE__), "", __FILE__) . "CLI/lib/io.moycroft.api/system/data/lockdown/" . "/status") == 1) {
    try {
        $account = new Account();
        if ($account->getRank() != 3) {
            echo "<style type=\"text/css\">
                    body {
                       margin: 0;
                       overflow: hidden;
                    }
                    #iframe1 {
                        position:absolute;
                        left: 0px;
                        width: 100%;
                        top: 0px;
                        height: 100%;
                    }
                    </style>
                    <title>Moycroft | Under Maintenance</title>
                    <iframe id=\"iframe1\" name=\"iframe1\" frameborder=\"0\"
                         src=\"" . $config['api']['CLI']['system']['maintLoc'] . "\"></iframe>  ";
            exit();
        }
    } catch (\Error $e) {
        echo "<style type=\"text/css\">
                    body {
                       margin: 0;
                       overflow: hidden;
                    }
                    #iframe1 {
                        position:absolute;
                        left: 0px;
                        width: 100%;
                        top: 0px;
                        height: 100%;
                    }
                    </style>
                    <title>Moycroft | Under Maintenance</title>
                    <iframe id=\"iframe1\" name=\"iframe1\" frameborder=\"0\"
                         src=\"" . $config['api']['CLI']['system']['maintLoc'] . "\"></iframe>  ";
        exit();
    }

}

/**
 * Memory Configuration:
 * This will allow for longer debugging scripts and tasks to run.
*/
ini_set('memory_limit', '1024M');

