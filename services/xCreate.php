<?php
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



require_once "../config.php";
$debugName=DBG_DIR."debug-create.txt";
if (file_exists('../data.json')){
    $f = fopen('../data.json','r');
    $t=fread($f,filesize('../data.json'));

    $_REQUEST=json_decode($t,true);
}

	require_once LIB_DIR."tbs_class.php";
	require_once LIB_DIR."tbs_plugin_opentbs.php";
	$TBS = new clsTinyButStrong; // new instance of TBS
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	
	//ACQUISIZIONE DATI DI REQUEST
	$filename = $_REQUEST["filename"];
	$modello=$_REQUEST["model"];
	$data=$_REQUEST["data"];

	$app=$_REQUEST["app"];
	$mode=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):("");
	$group=$_REQUEST["group"];
	$project=$_REQUEST["project"];
	$id=($_REQUEST["id"])?($_REQUEST["id"]):(rand(1,100000));

	//RIMOZIONE slashes del POST
	if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
		$_REQUEST = array_map( 'stripslashes', $_REQUEST);
    }
	$request=$_REQUEST;
	//DECODIFICA DELLA STRINGA JSON CON DATI
	//$_REQUEST['data']=json_decode($_REQUEST["data"],true);
	
	

	//DEBUG DEI DATI DI REQUEST
	debug($debugName,$_REQUEST,'a+'); 
	

	
	//MODELLO DI STAMPA
	if(filter_var($modello, FILTER_VALIDATE_URL)){
		$f=fopen($modello,'rb');
		$doc= stream_get_contents($f);
		fclose($f);
		$name=pathinfo($modello,PATHINFO_BASENAME);
		$modelName="/tmp/$name";
		$f=fopen($modelName,'w');
		if (fwrite($f,$doc)) debug($debugName,"File $name scritto correttamente",'a+'); 
		else{
			debug($debugName,"File $name non è scritto correttamente",'a+');
		}
		fclose($f);
	}
	else{
		$modelDir=($project)?(MODEL.$project.DIRECTORY_SEPARATOR):(MODEL);
		$modelName = ($group)?($modelDir.$app.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR.$modello):($modelDir.$app.DIRECTORY_SEPARATOR.$modello);
	}
	
	if(!file_exists($modelName)){
		debug($debugName,"Template $modelName not found!",'a+');
		print json_encode(Array("success"=>-1,""=>"$modelName"));
		return; 
	}

	if(!file_exists($modelName)){
		debug($debugName,"Template $modelName not found!",'a+');
		print json_encode(Array("success"=>-1,""=>"$modelName"));
		return; 
	}
	debug($debugName,"Mode : $mode\nLoading Template $modelName",'a+');
		
	$TBS->LoadTemplate($modelName);
	$TBS->SetOption('noerr',true);
	debug($debugName,"Template Loaded",'a+');
	$data=$_REQUEST["data"];
	
	switch($app){
		case "ordinanze":
			$keys=array_keys($data);
			$excludedItems=Array('numero_registro_cronologico','data_pubblicazione');
			foreach($excludedItems as $k ){
				if (in_array($k,$keys) && !$data[$k]){
					unset($data[$k]);
				}
			}
			debug($debugName,$data,'a+');
			break;
		default:
			$dbgName="get.debug";
	}
	
	if($data) {
		$data["oggi"]=date('d/m/Y');
	   $TBS->LoadTemplate($modelName);
	   $TBS->SetOption('noerr',true);
		array_walk_recursive($data, 'decode');
		mergeFields($TBS,$data);

	if ($TBS->Plugin(OPENTBS_FILEEXISTS, 'word/header1.xml')){
			$TBS->LoadTemplate('#word/header1.xml');
			mergeFields($TBS,$data);
		}
		if ($TBS->Plugin(OPENTBS_FILEEXISTS, 'word/footer1.xml')){
				$TBS->LoadTemplate('#word/footer1.xml');
				mergeFields($TBS,$data);
		}    
	}	
	$docDir=($project)?(DOC_DIR."$project".DIRECTORY_SEPARATOR):(DOC_DIR);
	if ($mode=="show"){
		$docFile = $docDir."$app/$id/$filename";
		if (!file_exists($docDir."$app")) mkdir($docDir."$app");
		if (!file_exists($docDir."$app/$id")) mkdir($docDir."$app/$id");
		$TBS->Show(OPENTBS_FILE,$docFile);		 
		//$TBS->Show(OPENTBS_STRING);
		//$out=$TBS->Source;
		$encode=mb_detect_encoding($out);

		$ff=fopen($docFile,'r');
		$doc=fread($ff,filesize($docFile));

		//$out=(mb_detect_encoding($item)=='UNICODE')?(utf8_encode($item)):($item);
		print $doc;
		return;
		//print json_encode(Array("success"=>1,'filename'=>'nofile',"output"=>$out));
		
		//return;
	}
	else{
		
		if (!file_exists($docDir."$app")) mkdir($docDir."$app");
		if (!file_exists($docDir."$app/$id")) {
			if (!mkdir($docDir."$app/$id")){
				debug($debugName,"Impossibile creare la directory ".$docDir."$app/$id",'a+');
			}
		}
        $docFile = $docDir."$app/$id/$filename";
        $TBS->Show(OPENTBS_FILE,$docFile);		 
		if($TBS){
			echo  json_encode(Array("success"=>1,'filename'=>$docFile));
			debug($debugName,"Document $docFile created",'a+');

		}
		else{
			echo json_encode(Array("success"=>-1,'filename'=>$docFile));
			debug($debugName,"Document $docFile created",'a+');
		}
		return;
	}
	
?>
