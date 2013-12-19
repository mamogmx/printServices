<?php

	require_once "config.php";
	
	require_once "tbs_class_php5.php";
	require_once "tbs_plugin_opentbs.php";
	$TBS = new clsTinyButStrong; // new instance of TBS
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	
	//ACQUISIZIONE DATI DI REQUEST
	$filename = $_REQUEST["filename"];
	$modello=$_REQUEST["model"];
	$data=$_REQUEST["data"];

	$app=$_REQUEST["app"];
	$mode=$_REQUEST["mode"];
	$group=$_REQUEST["group"];
	$id=($_REQUEST["id"])?($_REQUEST["id"]):(rand(1,100000));
	
	//RIMOZIONE slashes del POST
	if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
		$_REQUEST = array_map( 'stripslashes', $_REQUEST);
    }
	$request=$_REQUEST;
	//DECODIFICA DELLA STRINGA JSON CON DATI
	$_REQUEST['data']=json_decode($_REQUEST["data"],true);
	
	//DEBUG DEI DATI DI REQUEST
	ob_start();
	print_r($_REQUEST);
	$out=ob_get_contents ();
	ob_end_clean(); 

	$f = fopen (DBG_CREATION,'w');
	fwrite($f,date('H:m:s')."\n");
	fwrite($f,$out);
	
	//MODELLO DI STAMPA
	$modelName = ($group)?(MODEL."$app/$group/$modello"):(MODEL."$app/$modello");

	if(!file_exists($modelName)){
		fwrite($f,"\nTemplate $modelName not found!");
		print json_encode(Array("success"=>-1,""=>"$modelName"));
		return; 
	}
	fwrite($f,"\nMode : $mode");
	fwrite($f,"\nLoading Template $modelName");
	$TBS->LoadTemplate($modelName);
	$TBS->SetOption('noerr',true);
	fwrite($f,"\nTemplate Loaded");
	$data=$_REQUEST["data"];
	if($data) {
		$data["oggi"]=date('d/m/Y');
		array_walk_recursive($data, 'decode');
		$data["convoglio_tragitti_1"]=$data["convoglio_tragitti"];
		foreach($data as $key=>$value){
			if(is_array($value)){
				$TBS->MergeBlock($key, $value);
			}
			else{
				$TBS->MergeField($key, $value);
			}
		}
	}
	if ($mode=="show"){
		$docFile = DOC_DIR."$app/$id/$filename";
		if (!file_exists(DOC_DIR."$app")) mkdir(DOC_DIR."$app");
		if (!file_exists(DOC_DIR."$app/$id")) mkdir(DOC_DIR."$app/$id");
		$TBS->Show(OPENTBS_FILE,$docFile);		 
		//$TBS->Show(OPENTBS_STRING);
		//$out=$TBS->Source;
		$encode=mb_detect_encoding($out);
		fwrite($f,"Encoding : $encode");
		$ff=fopen($docFile,'r');
		$doc=fread($ff,filesize($docFile));
		fclose($f);
		//$out=(mb_detect_encoding($item)=='UNICODE')?(utf8_encode($item)):($item);
		print $doc;
		return;
		//print json_encode(Array("success"=>1,'filename'=>'nofile',"output"=>$out));
		
		//return;
	}
	else{
		
		if (!file_exists(DOC_DIR."$app")) mkdir(DOC_DIR."$app");
		if (!file_exists(DOC_DIR."$app/$id")) {
			if (!mkdir(DOC_DIR."$app/$id")){
				fwrite($f,"Impossibile creare la directory ".DOC_DIR."$app/$id");
			}
		}
        $docFile = DOC_DIR."$app/$id/$filename";
        $TBS->Show(OPENTBS_FILE,$docFile);		 
		if($TBS){
			echo  json_encode(Array("success"=>1,'filename'=>$docFile));
			fwrite($f,"\nDocument $docFile created");
		}
		else{
			echo json_encode(Array("success"=>-1,'filename'=>$docFile));
			fwrite($f,"\nError Document $docFile NOT created");
		}
		fclose($f);
		return;
	}
	
?>
