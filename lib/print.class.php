<?php


class printDoc{
    var $data;
    var $debugDir;
    var $errors = Array();
    var $model = FALSE;
    var $modelDir;
    var $outputDir;
    var $templates;
    var $tmpDir;
    var $type;
    var $TBS;

    function __construct($type='docx'){
        $this->type=$type;
        $this->debugDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."debug".DIRECTORY_SEPARATOR;
        $this->modelDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."modelli".DIRECTORY_SEPARATOR;
        $this->outputDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."documenti".DIRECTORY_SEPARATOR;
        $this->tmpDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
        $this->TBS = new clsTinyButStrong;                  // new instance of TBS
        $this->TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);    // load OpenTBS plugin
        $this->data = Array();
    }
    
    function testWriting(){
    	$arrayDir = Array("debug"=>$this->debugDir,"model"=>$this->modelDir,"output"=>$this->outputDir,"temporary"=>$this->tmpDir);
    	$filename = sprintf("%s.txt",$this->randomString());
    	echo "<p>Starting Writing Test......</p>";
    	echo "<ol>";
    	foreach($arrayDir as $name=>$dir){
    		$f = fopen($dir.$filename,'w');
    		$result = '<span style="color:red;font-weight:bold;">Failed</span>';
    		if (fwrite($f,"Test")){
    			$result = '<span style="color:green;font-weight:bold;">Success</span>';
    			//unlink($dir.$filename);
    		}
    		fclose($f);
    		$msg = sprintf("<li><p>Trying to write on $name folder %s:%s</p></li>",$dir,$result);
    		echo $msg;
    	}
    	echo "</ol>";
    }
    
    private function loadModelFromRL($url){
        $this->debug("loadModel.debug","Modello letto da url $url",'a+');
        $f=fopen($url,'rb');
        $doc= stream_get_contents($f);
        fclose($f);
        if(!$doc){
            $this->errors["loadModel"] = "URL $url does not exist!";
            return FALSE;
        }
        $filename = sprintf("%s%s.%s",$this->tmpDir,$this->randomString(),$this->type);
        $f=fopen($filename,'w');
        if (fwrite($f,$doc)) $this->debug("loadModel.debug","File $filename scritto correttamente",'a+'); 
        else{
            $this->errors["loadModel"]="Error, can't write file $filename.";
            return FALSE;
        }
        fclose($f);
        return $filename;
    }
    
    private function loadModelFromFile($file){
        $this->debug("loadModel.debug","Modello ricevuto con codifica base64",'a+');
        $doc = base64_decode($file);
        if(!$doc){
            $this->errors["loadModel"] = "URL $url does not exist!";
            return FALSE;
        }
        $filename = sprintf("%s%s.%s",$this->tmpDir,$this->randomString(),$this->type);
        $f=fopen($filename,'w');
        if (fwrite($f,$doc)) $this->debug("loadModel.debug","File $filename scritto correttamente",'a+'); 
        else{
            $this->errors["loadModel"]="Error, can't write file $filename.";
            return FALSE;
        }
        fclose($f);
        return $filename;
    }
    
    private function loadModelFromFileSystem($filename){
        $this->debug("loadModel.debug","Modello letto da filesystem $filename",'a+');
        if (file_exists($filename)){
            return $filename;
        }
        else{
            $this->errors["loadModel"] = "File $filename does not exist!";
            return FALSE;
        }
    }
    
    function loadModel($mode,$model){
        switch($mode){
        	case "file":
        		$filename = $this->loadModelFromFile($model);
        		break;
        	case "filesystem":
        		$filename = $this->loadModelFromFileSystem($model);
        		break;
        	case "http":
        		$filename = $this->loadModelFromURL($url);
        		break;
        	default:
        		$filename = FALSE;
        		$this->errors["loadModel"] = "\"$mode\" not avaiable";
        		break;
        }
        if ($filename === FALSE){
        	return $filename;
        }
        $this->model = TRUE;
        $this->TBS->loadTemplate($filename);
        $this->TBS->SetOption('noerr',true);
    }
    
    function loadData($data){
        if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
            $data = array_map( 'stripslashes', $data);
        }
        //DECODIFICA DELLA STRINGA JSON CON DATI
        $result=json_decode($data,true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $this->data = $result;
                return 1;
                break;
            case JSON_ERROR_DEPTH:
                $this->errors["loadData"]='Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $this->errors["loadData"]='Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $this->errors["loadData"]='Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $this->errors["loadData"]='Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $this->errors["loadData"]='Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $this->errors["loadData"]='Unknown error';
                break;
        }
        return FALSE;
    }
    
    private function loadDocx($file){
    	if (file_exists($file)){
    		$TBS = new clsTinyButStrong; // new instance of TBS
    		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
    		$TBS->LoadTemplate($file);
    		$TBS->PlugIn(OPENTBS_SELECT_MAIN);
    		$v = $TBS->GetBlockSource("source",false,false,false);
    		return $v;
    	}
    	else{
    		$this->errors["loadTemplates"][] = "Template $file not found";
    		return FALSE;
    	}
    }
    
    function loadAllDocx($datafiles=Array()){
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	
        $result=Array();
        foreach($datafiles as $key=>$files){
        	if (is_array($files)){
        		for($i=0;$i<count($files);$i++){
        			$file = $files[$i];
        			$res = $this->loadDocx($file);
        			if ($res !== FALSE){
        				$this->templates[$key][] = $res;
        			}
        		}
        	}
        	else{
        		$res = $this->loadDocx($files);
        		if ($res !== FALSE){
        			$this->templates[$key] = $res;
        		}
        	}  
        }
        if ($this->errors) $this->debug('loadTemplate.debug', $this->errors);
    }
    
    private function transformData(){
        $this->data["today"] = date("d/m/Y");
    }
    
    private function MergeFields(){
        foreach($this->data as $key=>$value){
            if(is_array($value)){
                $this->TBS->MergeBlock($key, $value);
            }
            else{
                $this->TBS->MergeField($key, $value);
            }
        }
    }
    
    private function mergeDocx(){
    	foreach($this->templates as $key=>$value){
    		$tmpFile = sprintf("%s%s.%s",$this->tmpDir,$this->randomString(),$this->type);
    		$this->TBS->Show(OPENTBS_FILE, $tmpFile);
    		$TBS = new clsTinyButStrong; // new instance of TBS
			$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
			$TBS->LoadTemplate($tmpFile,OPENTBS_ALREADY_XML);
			if(is_array($value)){
				$TBS->MergeBlock($key, $value);
			}
			else{
				$TBS->MergeField($key, $value);
			}
			$this->TBS = $TBS;
			//unlink($tmpFile);
			unset($TBS);
    	}
    }
    
    function createDocument($filename){
        if (!$this->model) return FALSE;
        
        $this->MergeFields();
        $this->mergeDocx();
        $r = $this->TBS->Show(OPENTBS_FILE, $filename);
        if(file_exists($filename)) return 1;
        else 
        	return FALSE;
    }
    
    function randomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
    
    private function debug($file,$data,$mode='w+'){
        $f=fopen($this->debugDir.$file,$mode);
        ob_start();
        print_r($data);
        $result=ob_get_contents();
        ob_end_clean();
        fwrite($f,$result."\n");
        fclose($f);
    }
    
}
?>