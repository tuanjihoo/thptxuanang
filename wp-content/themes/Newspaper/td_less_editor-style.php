<?php
//this is the less compiler used for the editor style


ob_start('ob_gzhandler');
header('Content-type: text/css');


require_once("includes/wp_booster/external/lessc.inc.php");
$less = new lessc;

echo $less->compileFile("includes/less_files/editor-style.less");

