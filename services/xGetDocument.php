<?php
require_once "../config.php";
$debugName=DBG_DIR."debug-read.txt";

debug($debugName,$_REQUEST,'w');

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
	header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        debug($debugName,"Il file $path con dimensione $size e Mime $mimetype");
	print $res;
	return;
}
else{
	debug($debugName,"Il file $path non è stato trovato");
	print "Il file $path non è presente";
	return;
}
?>
