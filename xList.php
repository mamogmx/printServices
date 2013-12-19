<?php
require_once "config.php";
debug(DBG_LIST,date("d/m/Y H:M:s"),'w');
$app=$_REQUEST["app"];
$group=$_REQUEST["group"];
$path=($group)?(MODEL."$app/$group/"):(MODEL."$app/");
$result=Array();
if (file_exists($path) && $handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_file($path."/".$entry)) {
			$result[]="$entry|$entry";
		}
    }
	closedir($handle);
}
sort($result);
debug(DBG_LIST,$result);
print json_encode($result);
return;
?>