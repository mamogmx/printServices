<?php
require_once "../config.php";
$debugName=DBG_DIR."debug-convert.txt";

$docURL=$_REQUEST["docurl"];
$filename=($_REQUEST["filename"])?($_REQUEST["filename"]):(array_pop(explode('/',$_REQUEST["docurl"])));

debug($debugName,$_REQUEST);

//LETTURA DEL FILE DA URL
$f=fopen($docURL,'rb');
$doc= stream_get_contents($f);
fclose($f);
//SCRITTURA DEL FILE IN LOCALE
$docName=DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename;
$tmp=explode('.',$filename);
array_pop($tmp);
$basename=implode('.',$tmp);
$filename=$basename.'.pdf';

$f=fopen($docName,'w');
if (fwrite($f,$doc)) fwrite($fDebug,"File $docName scritto correttamente. \n");
fclose($f);

//CONTROLLO CHE FILE SIA STATO SCRITTO
if (file_exists($docName) && filesize($docName)){
	
	$cmd="HOME=/tmp/pdfout /home/silvio/libreoffice3.6/program/soffice \"-env:UserInstallation=file:///tmp/pdfout\" --headless --invisible --nologo --convert-to pdf $docName --outdir /tmp";
	$res=exec($cmd);
	$msg1="Overwriting:";// $dirname/$filename";
	$msg2="convert";// $dirname/$filename";
	if (stripos($res,$msg1)===FALSE and stripos($res,$msg2)===FALSE)
		echo json_encode(Array("success"=>0,"message"=>$res));
	else{
		$pdfName=str_replace('.odt','',str_replace('.docx','',$docName)).".pdf";
		$f = fopen($pdfName,'r');
		$text=fread($f,filesize($pdfName));
		fclose($f);
		if ($_REQUEST['mode']=="show"){
			unlink($docName);
			unlink($pdfName);
			header('Content-type: application/pdf');
			print $text;
			return;
			//print json_encode(Array("success"=>1,"filename"=>$text));
		}
		else{
			
			$app=$_REQUEST["app"];
			$id=$_REQUEST["id"];
			$date=$_REQUEST["data"];
			
			$d=extractDate($date);
			$appDir=DOC_DIR.$app;
			if (!file_exists($appDir)) mkdir($appDir);
			if($d){
				$yearDir=$appDir.DIRECTORY_SEPARATOR.$d["year"];
				if (!file_exists($yearDir)) mkdir($yearDir);
				$appDir=$yearDir;
			}
			$folderDir=$appDir.DIRECTORY_SEPARATOR.$id;
			if (!file_exists($folderDir)) mkdir($folderDir);
			$location=$folderDir.DIRECTORY_SEPARATOR.$filename;
			$f=fopen($location,'w');
			fwrite($f,$text);
			fclose($f);
			unlink($docurl);
			echo json_encode(Array("success"=>1,"location"=>$location));
			return;
		}
			
	}
		
		
}
else
	echo json_encode(Array("success"=>0,"message"=>"FILE \"$dirname/$filename\" NOT FOUND"));

?>