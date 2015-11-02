<?php

class printDoc{
    var $data;
    var $errors = Array();
    var $model;
    var $templates;
    var $tmpDir;
    var $type;
    var $TBS;

    function __construct($type='docx'){
        $this->type=$type;
        $this->tmpDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
        $this->debugDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."debug".DIRECTORY_SEPARATOR;
        $this->TBS = new clsTinyButStrong;                  // new instance of TBS
        $this->TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);    // load OpenTBS plugin
        $this->data = Array();
    }
    
    private function loadModelFromHTTP($url){
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
    
    private function loadModel($mode){
        
        
        $this->TBS->loadTemplate($filename);
        $TBSTemp->SetOption('noerr',true);
    }
    
    private function loadData($data){
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
    
    private function loadDocx($files=Array()){
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
	
        $result=Array();
        foreach($files as $file){
            if (file_exists($file)){
                $TBS->LoadTemplate($file);
                $TBS->PlugIn(OPENTBS_SELECT_MAIN);
                $v = $TBS->GetBlockSource("source",false,false,false);
                $this->templates[]= $v;
            }
            else{
                $this->errors["loadTemplates"][] = "Template $file not found";
            }
            
        }
    }
    
    function transformData(){
        $this->data["today"] = date("%d/%m/%Y");
    }
    
    function MergeFields(){
        foreach($this->data as $key=>$value){
            if(is_array($value)){
                $this->TBS->MergeBlock($key, $value);
            }
            else{
                $this->TBS->MergeField($key, $value);
            }
        }
    }
    
    function mergeDocx(){
        $T=$this->TBS;
    }
    
    function createDocument(){
        
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