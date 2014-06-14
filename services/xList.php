<?php
require_once "../config.php";
$debugName=DBG_DIR.DBG_LIST;
debug($debugName,$_REQUEST,'w');
$app=$_REQUEST["app"];
$group=$_REQUEST["group"];
$project=$_REQUEST["project"];

$modelDir=($project)?(MODEL.$project.DIRECTORY_SEPARATOR):(MODEL);
$path=($group)?($modelDir.$app.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR):($modelDir.$app.DIRECTORY_SEPARATOR);
$result=Array();
if (file_exists($path) && $handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_file($path."/".$entry)) {
			$result[]="$entry|$entry";
		}
    }
	closedir($handle);
}
else{
	$result=Array("Directory non trovata|Directory non trovata");
}
sort($result);
debug($debugName,$result);
print json_encode($result);
return;
?>