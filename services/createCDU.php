<?php

require_once "../config.php";

function randomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function getZone($zone,$project,$app="cdu"){
	$dir = MODEL_DIR.DIRECTORY_SEPARATOR.$project.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR."normativa".DIRECTORY_SEPARATOR;
	$TBS = new clsTinyButStrong; // new instance of TBS
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	
	$result=Array();
	foreach($zone as $zona){
		$filename=sprintf("%sZona %s.docx",$dir,$zona);
		if (file_exists($filename)){
			$TBS->LoadTemplate($filename);
			$TBS->PlugIn(OPENTBS_SELECT_MAIN);
			$v = $TBS->GetBlockSource("source",false,false,false);
			$result[]= $v;
			//if ($v) echo "<p>Found normativa $zona in file $filename</p>";
		}
		/*else{
			echo "<p>No File Found $filename</p>";
		}*/
		
	}
	return $result;
}


function mergeFields($T,$data){
    foreach($data as $key=>$value){
        if(is_array($value)){
                $T->MergeBlock($key, $value);
        }
        else{
                $T->MergeField($key, $value);
        }
    }
}


//ACQUISIZIONE DATI DI REQUEST
$filename = (array_key_exists("filename", $_REQUEST))?($_REQUEST["filename"]):("");
$modello=(array_key_exists("model", $_REQUEST))?($_REQUEST["model"]):("");
$data=(array_key_exists("data", $_REQUEST))?($_REQUEST["data"]):(Array());


$app=(array_key_exists("app", $_REQUEST))?($_REQUEST["app"]):("cdu");
$mode=(array_key_exists("mode", $_REQUEST))?($_REQUEST["mode"]):("");
$group=(array_key_exists("group", $_REQUEST))?($_REQUEST["group"]):("");
$project=(array_key_exists("project", $_REQUEST))?($_REQUEST["project"]):("");
//$id=($_REQUEST["id"])?($_REQUEST["id"]):(rand(1,100000));

//RIMOZIONE slashes del POST
if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
    $_REQUEST = array_map( 'stripslashes', $_REQUEST);
}
$request=$_REQUEST;
//DECODIFICA DELLA STRINGA JSON CON DATI
$_REQUEST['data']=json_decode($_REQUEST["data"],true);

//DEBUG DEI DATI DI REQUEST
debug($debugName,$_REQUEST,'a+');

//MODELLO DI STAMPA
if (!$modello){
    $msg="Nessun Parametro \"model\" passato al servizio";
    $result=Array("success"=>0,"message"=>$msg);
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($result);
    return;
}

elseif(filter_var($modello, FILTER_VALIDATE_URL)){
    $f=fopen($modello,'rb');
    $doc= stream_get_contents($f);
    fclose($f);
	if(!$doc){
		$msg="Il modello $name non è stato recuperato correttamente dalla url $modello";
        debug($debugName,$msg,'a+');
        $result=Array("success"=>0,"message"=>$msg);
        header('Content-Type: application/json; charset=utf-8');
        print json_encode($result);
        return;
	}
    $name=pathinfo($modello,PATHINFO_BASENAME);
    $modelName="/tmp/$name";
    $f=fopen($modelName,'w');
    if (fwrite($f,$doc)) debug($debugName,"File $name scritto correttamente",'a+'); 
    else{
        $msg="Il modello $name non è stato scritto correttamente sul server";
        debug($debugName,$msg,'a+');
        $result=Array("success"=>0,"message"=>$msg);
        header('Content-Type: application/json; charset=utf-8');
        print json_encode($result);
        return;
    }
    fclose($f);
}
elseif(base64_decode($modello)!==FALSE){
    debug($debugName,"Modello ricevuto da servizio","a+");
    $name=$filename;
    $modelName="/tmp/$name";
    $doc = base64_decode($modello);
    $f=fopen($modelName,'w');
    if (fwrite($f,$doc)) debug($debugName,"File $name scritto correttamente",'a+'); 
    else{
        $msg="Invio File Modello.\nIl modello $name non è stato scritto correttamente sul server";
        debug($debugName,$msg,'a+');
        $result=Array("success"=>0,"message"=>$msg);
        header('Content-Type: application/json; charset=utf-8');
        print json_encode($result);
        return;
    }
    fclose($f);

}
else{
    $modelDir=($project)?(MODEL_DIR.$project.DIRECTORY_SEPARATOR):(MODEL_DIR);
    $modelName = ($group)?($modelDir.$app.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR.$modello):($modelDir.$app.DIRECTORY_SEPARATOR.$modello);
}

$filename=($filename)?($filename):($modello);

if(!file_exists($modelName)){
    $msg="Im modello $modelName non è stato trovato!";
    debug($debugName,$msg,'a+');
    $result=Array("success"=>0,"message"=>$msg);
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($result);
    return;
}

debug($debugName,"Mode : $mode\nLoading Template $modelName",'a+');

$normativa = ($_REQUEST["normativa"])?(json_decode($_REQUEST["normativa"])):(Array());

$TBS = new clsTinyButStrong; // new instance of TBS
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

$TBSTemp = new clsTinyButStrong; // new instance of TBS
$TBSTemp->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

if($normativa){
    $template = implode("",getZone($normativa,$project,$app));
}


if($data) {
    $tmpFile = sprintf("%s.docx",randomString());
    $data["oggi"]=date('d/m/Y');
    $TBSTemp->LoadTemplate($modelName);
    $TBSTemp->SetOption('noerr',true);
    array_walk_recursive($data, 'decode');
    mergeFields($TBSTemp,$data);
    
    $TBSTemp->Show(OPENTBS_FILE, $tmpFile);
   
    if($template){
        $TBS->LoadTemplate($tmpFile,OPENTBS_ALREADY_XML);
        $TBS->MergeField("normativa",$template);
        unlink($tmpFile);
    }
    else{
        $TBS = $TBSTemp;
    }
}	


$docDir=($project)?(DOC_DIR."$project".DIRECTORY_SEPARATOR):(DOC_DIR);
switch($mode){
    case "show":
        $docFile = $docDir."$app/$id/$filename";
        if (!file_exists($docDir."$app")) mkdir($docDir."$app");
        if (!file_exists($docDir."$app/$id")) mkdir($docDir."$app/$id");
        $TBS->Show(OPENTBS_FILE,$docFile);		 
        $ff=fopen($docFile,'r');
        $doc=fread($ff,filesize($docFile));
        fclose($ff);
        header('Content-Type: application/application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        print $doc;
        return;
        break;
    default :
        $docFile = $docDir."$app/$id/$filename";
        if (!file_exists($docDir."$app")) mkdir($docDir."$app");
        if (!file_exists($docDir."$app/$id")) {
            if (!mkdir($docDir."$app/$id")){
                $msg="Impossibile creare la directory ".$docDir."$app/$id";
                $result=Array("success"=>0,"message"=>$msg);
                debug($debugName,$msg,'a+');
                header('Content-Type: application/json; charset=utf-8');
                print json_encode($result);
                return;
            }
        }
        $TBS->Show(OPENTBS_FILE,$docFile);
        if($TBS){
            if($mode=="download"){
                $result=Array("success"=>1,'filename'=>$docFile);
            }
            else{
                $f=fopen($docFile,'r');
				$fsize=filesize($docFile);
                $text=fread($f,$fsize);
                fclose($f);
                $result=Array("success"=>1,'filename'=>$docFile,"file"=>  base64_encode($text),"size"=>$fsize);
            }
            $msg="Il file $filename è stato creato correttamente";
            debug($debugName,$msg,'a+');
            header('Content-Type: application/json; charset=utf-8');
            print json_encode($result);
            return;
        }
        else{
            $msg="Sono stati riscontrati degli errori nella generazione del documento $filename";
            debug($debugName,$msg,'a+');
            $result=Array("success"=>0,"message"=>$msg);
            header('Content-Type: application/json; charset=utf-8');
            print json_encode($result);
            return;
        }
        break;
}

?>