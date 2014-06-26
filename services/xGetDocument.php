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
	$path_parts = pathinfo($path);
	$fsize=filesize($path);
	$f=fopen($path,'r');
	$res=fread($f,$fsize);
	fclose($f);
	//header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        debug($debugName,"Il file $path con dimensione $fsize e Mime $mimetype");
        switch ($ext) {
            case "pdf":
            header("Content-type: application/pdf"); 
            //header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
            break;
            default: // Other document formats (doc, docx, odt, ods etc)
                header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                //header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
                
        }
        header("Content-length: $fsize");
	echo $res;
}
else{
        
	debug($debugName,"Il file $path non è stato trovato");
	print "Il file $path non è presente";
	return;
}
?>
