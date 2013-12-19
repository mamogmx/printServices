<?php

function decode(&$item, &$key){
    	$item=(mb_detect_encoding($item)=='UTF-8')?(utf8_decode($item)):($item);
		$item=str_replace('<br/>',"",$item);
	/*if ((strtotime($item)) !== false && !is_numeric($item)) {
		$timestamp = strtotime($item);
		$item = date('d/m/Y', $timestamp);
	} 
	$item=(preg_match('/^(\d{4})\/(\d{2})\/(\d{2}) (\d{2}):(\d{2}):(\d{2})/'))?(array_shift(explode(' ',$item))):($item);
	$f = fopen ('debug.txt','a+');
	fwrite($f,"$key : $item Encoding : ".mb_detect_encoding($item)."\n");
	fclose($f);*/
}

	$f = fopen ('pippo.txt','w');
	fwrite($f,date('H:m:s')."\n");
	if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
		$_REQUEST = array_map( 'stripslashes', $_REQUEST);
    }
	$request=$_REQUEST;
	$_REQUEST['data']=json_decode($_REQUEST["data"],true);
	ob_start();
	//print_r($request);
	print_r($_REQUEST);
	$out=ob_get_contents ();
	ob_end_clean();  
	fwrite($f,$out);
	require_once "tbs_class_php5.php";
	require_once "tbs_plugin_opentbs.php";
	$filename = $_REQUEST["filename"];
	$modello=$_REQUEST["model"];
	$data=$_REQUEST["data"];
	//foreach($data as $k=>$v) fwrite($f,"\n$k = $v");
	$app=$_REQUEST["app"];
	$mode=$_REQUEST["mode"];
	$group=$_REQUEST["group"];
	$id=($_REQUEST["id"])?($_REQUEST["id"]):(rand(1,100000));
	define('MODEL_DIR','/modelli/');
	define('DOC_DIR','/documenti/');
	$TBS = new clsTinyButStrong; // new instance of TBS
	$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	chdir ('../');	
	$serviceDir = getcwd();
	$modelName = $serviceDir.MODEL_DIR."$app/$modello";
	if($group) $modelName = $serviceDir.MODEL_DIR."$app/$group/$modello";


	//print json_encode($data);return;
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
	//$data=array_map('utf8_decode',$data);
	if($data) {
		$data["oggi"]=date("d/m/Y");
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
		$docFile = $serviceDir.DOC_DIR."$app/$id/$filename";
		if (!file_exists($serviceDir.DOC_DIR."$app")) mkdir($serviceDir.DOC_DIR."$app");
		if (!file_exists($serviceDir.DOC_DIR."$app/$id")) mkdir($serviceDir.DOC_DIR."$app/$id");
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
		
		if (!file_exists($serviceDir.DOC_DIR."$app")) mkdir($serviceDir.DOC_DIR."$app");
		if (!file_exists($serviceDir.DOC_DIR."$app/$id")) {
			if (!mkdir($serviceDir.DOC_DIR."$app/$id")){
				fwrite($f,"Impossibile creare la directory ".$serviceDir.DOC_DIR."$app/$id");
			}
		}
        $docFile = $serviceDir.DOC_DIR."$app/$id/$filename";
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
