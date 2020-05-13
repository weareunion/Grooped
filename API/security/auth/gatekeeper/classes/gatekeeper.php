<?php
//if( !( isset($_SESSION) ) ){ session_start(); }

/**
 *
 * @name API/security/auth/gatekeeper/classes/gatekeeper.php
 * @description: This class and this file in tandem make sure that the user stays authorised and prevents the user from making changes when not authorised both client-side and server-side.
 * @package API.security.auth.gatekeeper
 * @api General Backend API
 * @api Security API
 * @copyright © Cor3 Design, LLC. 2019-2020. All Rights Reserved.
 * @author Karl Schmidt
 */
namespace Moycroft\API\security\auth\gatekeeper;
use Moycroft\API\accounts\Account\Account;
use Moycroft\API\accounts\Activity\Activity;
use function Moycroft\API\helper\IMPORT;

define('CONFIG_PREVENTIMPORTS', true);
require_once dirname(__FILE__)."/../../../../config.php";
IMPORT("API.accounts.*");
$account = new Activity();
ob_start();


if(isset($_POST['security_microauth_requestStatus'])){
    if ($_SESSION['logged_in'] == true && isset($_SESSION['user_account'])) {
        $account->setActivity();
        echo true;
    }else{
        $account->setActivity(null,0);
        $_SESSION['microauth_redirect'] = $_SERVER['REQUEST_URI'];
        echo "0";
    }
    exit();
}



class Security_MicroAuth{
    public function __construct()
    {

    }
    /**
     * Function createJavascript()
     *
     * Generates JS block for user notification
     *
     * @author Karl Schmidt
     * @copyright © Cor3 Design, LLC. 2019-2020. All Rights Reserved.
     */
    public function createJavascript($importJQ=false){
        if ($importJQ){
           echo '<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>';
        }
        echo '
        <script>
        var _generatedScript_auth_microauth_data_interval = 1000; 
            function _generatedScript_auth_microauth_function_request() {
                $.ajax({
                        type: \'POST\',
                        url: window.location,
                        data: {\'security_microauth_requestStatus\': \'go\'},
                        success: function (status) {
                            if (status == 0){
                                window.location =  window.location.href.split("?")[0]  + "?security_microauth_lockdown&action=go";
                            } 
                        }
                        ,
                        complete: function () {
                                setTimeout(_generatedScript_auth_microauth_function_request, _generatedScript_auth_microauth_data_interval);
                        }
                });
            }
            setTimeout(_generatedScript_auth_microauth_function_request, _generatedScript_auth_microauth_data_interval);
        </script>
        
        ';

    }

    public function checkOnload(){
        global $microauth_config;
        if ((isset($_GET['security_microauth_lockdown']) && $_GET['action'] === 'go' && !isset($_SESSION['logged_in']) && !isset($_SESSION['user_account'])) || (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true)){
            if (!headers_sent()) {
                foreach (headers_list() as $header)
                    header_remove($header);
            }

			$encode = substr(urlencode($_SERVER['REQUEST_URI']), 0, strpos(urlencode($_SERVER['REQUEST_URI']), "."));
            header("Location: " . GLOBAL_CONFIG["api"]["security"]["auth"]["gatekeeper"]["throwout_addr"] . "?redirect=" . $encode);
            exit();
        }

    }
}

$security_microauth = new Security_Microauth();

$security_microauth -> checkOnload();

?>