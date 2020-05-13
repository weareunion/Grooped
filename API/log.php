<?php
session_start();
if (isset($_GET["clear"])){
    $_SESSION['dev.log'] = "";
    header("Location: log.php?notify");
}
echo "<h1>PASSIVE LOG HISTORY FOR CURRENT SESSION</h1>";

if (isset($_GET["disable"])){
    $_SESSION['internal.API.dev.verbose'] = false;
}
if (isset($_GET["enable"])){
    $_SESSION['internal.API.dev.verbose'] = true;
}
if ($_SESSION['internal.API.dev.verbose']){
    echo "<h2>Passive Logging: <span style=\"color: springgreen\">ON</span></h2>";
}else{
    echo "<h2>Passive Logging: <span style=\"color: orangered\">OFF</span></h2>";
}

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqtree/1.4.10/jqtree.css" />

<a name="top"></a>
<hr>
<a href="?clear">Clear</a>

<?php if ($_SESSION['internal.API.dev.verbose']){
    echo '<a href="?disable">Disable Logging</a>';
}else{
    echo '<a href="?enable">Enable Logging</a>';
}
?>
&nbsp
<a href="?refresh">Refresh</a>
<a href="#bottom">Tail Log</a>
<?php
echo "<b>&nbsp >>> CONSOLE: &nbsp<span style=\"color: deepskyblue\" id=\"console\"></span>";
if (isset($_GET['notify'])){
    echo "<span style=\"color: deepskyblue\"> Log Cleared.</span><br>";
}
if (isset($_GET['disable'])){
    echo "<span style=\"color: deepskyblue\"> Logging Disabled.</span><br>";
}
if (isset($_GET['enable'])){
    echo "<span style=\"color: deepskyblue\"> Logging Enabled.</span><br>";
}
if (isset($_GET['refresh'])){
    echo "<span style=\"color: deepskyblue\"> Log refreshed.</span><br>";
}
?>
    </b>
    <hr>
<br><br>
<?
echo $_SESSION['dev.log'];
?>
<div id="JSVIEW"></div>

<script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqtree/1.4.10/tree.jquery.js"></script>
<a name="bottom"></a>
<a href="#top">Jump to Top</a>


