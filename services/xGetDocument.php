<?php
require_once "../config.php";
$debugName=DBG_DIR."debug-read.txt";

debug($debugName,$_REQUEST);

$app=$_REQUEST['app'];
$id=$_REQUEST['id'];
$fileName=$_REQUEST['filename'];
$project=$_REQUEST["project"];


$docDir=($project)?(DOC_DIR.$project.DIRECTORY_SEPARATOR):(DOC_DIR);
$path=$docDir.$app.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$fileName;

if(file_exists($path)){
	require_once("../lib/MimeReader.php");
	$mime = new MimeReader($path);
	$mimetype = $mime->get_type(); 
	$size=filesize($path);
	$f=fopen($path,'r');
	$res=fread($f,$size);
	fclose($f);
	header("Content-type: $mimetype");
	print $res;
	return;
}
else{
	
	print "Il file $path non è presente";
	return
}
?>
