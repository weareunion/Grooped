<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-21
 * Time: 15:16
 */

namespace Moycroft\API\UI;
use Moycroft\API\accounts\Account\Account;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;
use function PHPSTORM_META\elementType;
require_once str_replace("/UI.php", "/../../config.php", __FILE__);
class UI
{

    private $depth = "";
    private $delay = 0;
    private $addContainer = true;

    public function getAnimationDelay($style="", $reset=false){
        if ($reset){
            $this->delay = 0;
        }
        $retVal = ("style=\"".$style."; animation-delay: ".$this->delay."s\"");
        $this->delay = $this->delay + .1;
        return $retVal;
    }
    public function asRetval($tf){
        $this->asRetVal = $tf;
    }
    public function generateErrorPage($reportCode, $title="Whoops! We have a problem", $message="We have encountered a problem on our end. We have dispatched a team of hyper-smart raccoons to take care of the issue."){
        $toPublish = "";
        $toPublish .= $this->header("Error", "We have a Problem","white", true);
        $toPublish .= $this->addComponent("pages/error","main",["errorcode" => $reportCode['report'], "title" => $title, "message" => $message, "shortcode" => $reportCode['short']],$this);
        $toPublish .= $this->postBody();
        return $toPublish;
    }
    public function header($windowName, $windowSubAction=null, $favIconColor="white", $addContainer=true){
        $this->instant = false;
        $this->addContainer = $addContainer;
        $retVal = "<html><head>";
        $retVal .= '
             <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
             <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/white/pace-theme-flash.min.css" />
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
        $retVal .= $this->windowName($windowName, $windowSubAction);
        $retVal .= $this->favIcon($favIconColor);
        $retVal .= "</head>";
        $retVal .= "<style media=\"screen\">
@keyframes shimmerBackground {
    0% {background-position:-5000px 0}
    100% {background-position:5000px 0}
}
 @keyframes placeHolderShimmer{

        0% {
            background-position: -468px 0;
        }
        100% {
            background-position: 468px 0;
        }
    }
.narrow.placeholder {
        -webkit-animation-duration: 1s;
        -webkit-animation-fill-mode: forwards;
        -webkit-animation-iteration-count: infinite;
        -webkit-animation-name: placeHolderShimmer;
        -webkit-animation-timing-function: linear;
        background: #f6f7f9;
        background-image: linear-gradient(to right, #f6f7f9 0%, #e9ebee 20%, #f6f7f9 40%, #f6f7f9 100%);
        background-repeat: no-repeat;
        /*background-size: 800px 104px;*/
        /*height: 104px;*/
        position: relative;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
        box-shadow: none !important;
    }
.shimmer
{
background-image: -moz-linear-gradient(160deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 25%, rgba(255,255,255,0.85) 60%, rgba(255,255,255,0) 100%);
background-image: -webkit-gradient(linear, left top, right top, color-stop(0%,rgba(255,255,255,0)), color-stop(25%,rgba(255,255,255,0)), color-stop(60%,rgba(255,255,255,0.85)), color-stop(100%,rgba(255,255,255,0)));
background-image: -webkit-linear-gradient(160deg, rgba(255,255,255,0) 0%,rgba(255,255,255,0) 25%,rgba(255,255,255,0.85) 60%,rgba(255,255,255,0) 100%);
background-image: -o-linear-gradient(160deg, rgba(255,255,255,0) 0%,rgba(255,255,255,0) 25%,rgba(255,255,255,0.85) 60%,rgba(255,255,255,0) 100%);
background-image: -ms-linear-gradient(160deg, rgba(255,255,255,0) 0%,rgba(255,255,255,0) 25%,rgba(255,255,255,0.85) 60%,rgba(255,255,255,0) 100%);
background-image: linear-gradient(160deg, rgba(255,255,255,0) 0%,rgba(255,255,255,0) 25%,rgba(255,255,255,0.85) 60%,rgba(255,255,255,0) 100%);
background-repeat: repeat-y;
background-position:-5000px 0;
animation: shimmerBackground 8s linear infinite;
}

.blog-post {
  &__headline {
    font-size: 1.25em;
    font-weight: bold;
  }

  &__meta {
    font-size: 0.85em;
    color: #6b6b6b;
  }
}

// OBJECTS

.o-media {
  display: flex;
  
  &__body {
    flex-grow: 1;
    margin-left: 1em;
  }
}

.o-vertical-spacing {
  > * + * {
    margin-top: 0.75em;
  }
  
  &--l {
    > * + * {
      margin-top: 2em;
    }
  }
}
            .orangeC {
                background-color: #E05C33;
            }
        
            .orangeCText {
                color: #E05C33;
            }
        
            .resm {
                margin: 0;
                padding: 0;
            }
        
            .center-div {
                position: absolute;
                margin: auto;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                width: 30%;
                height: 25%;
            }
        
            .center-results {
                position: relative;
                margin: auto;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 0%;
            }
        
            .center-search {
                position: absolute;
                margin: auto;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                width: 60%;
                height: 6%;
            }
        
            .disable {
                opacity: 0.6;
                pointer-events: none;
            }
            .wrapper {
                position: relative;
                min-height: 100vh;
            }
        
            [class|=\"confetti\"] {
                position: absolute;
            }
            .bgBlue {
              background: #223D49;
            }
            .bgBlue .lighten-1 {
              background: #294c5b;
            }
            .bgBlue .darken-1 {
              background: #1c333d;
            }
        
            body {
                background: #223D49;
            }
        </style>
        <body class=\"wrapper\">";
        if ($this->addContainer){
            $retVal .= "<div class=\"container\">";
        }
        return $retVal;
    }

    /**
     * Imports client-side plugins
     * @param $name - Name of Plugin
     * @param $component - Name of subdirectory (usually "js" or "html")
     * @param $UI - UI object from parent page
     */
    public function addPlugin($name, $plugin, $UI=null){
        if ($UI === null){
            $UI = $this;
        }

            $pluginLocation = dirname(__FILE__) . '/..' .'/assets/plugins/'.$plugin.'/'.$name.'.plugin';
            if (file_exists($pluginLocation)){
                include_once ($pluginLocation);
                __infoSH("Sucessfully added plugin \"$name\" as \"$plugin\". ", "success");
                return 1;
            }else{
                __error("This plugin does not exist.", true);
                return 0;
            }


    }
    public function addScript($name, $service, $UI=null){
        if ($UI === null){
            $UI = $this;
        }
        $pluginLocation = dirname(__FILE__) . '/..' .'/assets/scripts/'.$service.'/'.$name.'.script';
        if (file_exists($pluginLocation)){
            include_once ($pluginLocation);
            __infoSH("Sucessfully imported script \"$name\" from \"$service\". ", "success");
            return 1;
        }else{
            __error("This script does not exist in $pluginLocation.", true);
            return 0;
        }

    }
    public function getAvatar($userID=null, $size="tiny"){
        $accounts = new Account();
        return $accounts->getProfilePicture($userID, $size);
    }
    public function addComponent($type, $name, $options, $UI=null, &$componentID=""){

//        $retVal
        if ($UI === null){
            $UI = $this;
        }

        $pluginLocation = dirname(__FILE__) . '/..' .'/assets/components/'.$type.'/'.$name.'.component';


        if (file_exists($pluginLocation)){

            $toDisplay = file_get_contents($pluginLocation);


            $prefSplit = explode("}!}", $toDisplay);
            $header = true;
            if (sizeof($prefSplit) === 1){
                $header = false;

                $prefSplit[1] = $prefSplit[0];
            }


            foreach ($options as $key => $value){
                $prefSplit[1] = str_replace("{{{$key}}}", $value, $prefSplit[1]);
            }

            if ($header) {
                foreach (explode("\n", $prefSplit[0]) as $preference) {
                    if (substr($preference, 0, 2) !== "##") {
                        if (substr($preference, 0, 1) === "*") {
                            $pair = explode(":", $preference);

                            $pair[0] = str_replace("*", "", $pair[0]);
                            if ($pair[1] === "NULL") {
                                $pair[1] = "";
                            }

                            $prefSplit[1] = str_replace("{{{$pair[0]}}}", rtrim($pair[1]), $prefSplit[1]);
                        }
                    }

                }
            }


            $toRun = explode("{<{", $prefSplit[1]);
            $newOut = "";
            foreach ($toRun as $potent){
                if (strpos($potent, "}>}")){
                    $potentAfter = explode("}>}", $potent);

                    try {
                        $newOut .= eval($potentAfter[0]);

                    }catch (\ParseError $e){
                        echo $e->getMessage();
                    }
                    $newOut .=  $potentAfter[1];

                }else{
                    $newOut .= $potent;
                }
            }
            $componentID = GUID("API.UI.components", "HTML component ID", true);
            $linker = bin2hex(random_bytes(64));
            $newOut = str_replace("{{COMPONENTID}}", $componentID , $newOut);
            $newOut = str_replace("{{LINK}}", $linker , $newOut);
            if (strpos($newOut, '{{') === false){
                __infoSH("Sucessfully added component \"$name\" from \"$type\" . ", "success");
                return($newOut);
            }else{
                __infoSH("Component not published: Not all options given.", "warning");
            }

        }else{
            __error("This component does not exist.", true);
        }
//        $this->build();
        return false;
    }
    public function startContainer(){

        $this->addContainer = true;
        return "<div class=\"container\">";

    }
//    public function createTitle($title, $size=4,$subtitle="",)
    public function postBody(){
        $out = "";
        if ($this->addContainer){
            $out .= "</div>";
        }
        $out .= "</div></body>

            
            <script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>
            <script src=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js\"></script>
            <script src=\"https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js\"></script>
            <script src=\"https://cdn.jsdelivr.net/npm/push.js@1.0.9/bin/push.min.js\"></script>
            <script
                    src=\"https://code.jquery.com/ui/1.12.1/jquery-ui.min.js\"
                    integrity=\"sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=\"
        crossorigin=\"anonymous\"></script> <script>M.AutoInit()</script>";
        echo "<script>";
        $this ->addScript("package", "JSlib", $this);
        echo "</script>";
        echo "<script>";
        echo "Moycroft.init();";
        echo "</script>";
        return $out;
    }
    public function instant($on){
        $this->instant = $on;
    }
    public function windowName($name, $subaction){
        if (isset($subaction)){
            $subaction = " - " . $subaction;
        }else{
            $subaction = "";
        }
        return("<title>Cor3 | $name $subaction</title>");
    }
    public function assetsFolderLevel($depth){
        for ($x = 0; $x < $depth; $x++){
            $this->depth .= "../";
        }
    }
    public function getSwitchBoard(){
        return  "http".(!empty($_SERVER['HTTPS'])?"s":"").
            "://".$_SERVER['SERVER_NAME']."/".GLOBAL_CONFIG["api"]["UI"]["switchboard"]["location"] . "/switchboard.php";
    }
    public function getAssetBucket(){
        return  "http".(!empty($_SERVER['HTTPS'])?"s":"").
            "://".$_SERVER['SERVER_NAME']."/".GLOBAL_CONFIG["api"]["UI"]["switchboard"]["location"] . "/assets/";
    }
    public function favIcon($color="white"){
        return('<link rel="shortcut icon" type="image/png" href="'.$this->depth.'assets/img/favicon/'.$color.'.png"/>');
    }

}