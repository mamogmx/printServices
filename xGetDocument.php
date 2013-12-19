<?php
$app=$_REQUEST['app'];
$id=$_REQUEST['id'];
$fileName=$_REQUEST['filename'];
$fDebug=fopen('dbg_get.txt','w');

$path="/apps/printService/documenti/$app/$id/$fileName";

fwrite($fDebug,"Filename : $fileName\n");
fwrite($fDebug,"App : $app\n");
fwrite($fDebug,"Id : $id\n");
fwrite($fDebug,"Path : $path\n");
if(file_exists($path)){
	//$finfo = finfo_open(FILEINFO_MIME_TYPE);
	//$mimetype=mime_content_type($path);
	
	//finfo_close($finfo);
	$mimetype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	$size=filesize($path);
	fwrite($fDebug,"Mime Type : $mimetype\n");
	fwrite($fDebug,"Size : $size\n");
	$f=fopen($path,'r');
	$res=fread($f,$size);
	fclose($f);
	header("Content-type: $mimetype");
	print $res;
	return;
}
else{
	fwrite($fDebug,"File $path not Found");
	print "-1";
}
fclose($fDebug);
?>
