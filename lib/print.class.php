<?php

class utilsPrint {
    
    static function mergeFields($T,$data){
        foreach($data as $key=>$value){
            if(is_array($value)){
                    $T->MergeBlock($key, $value);
            }
            else{
                    $T->MergeField($key, $value);
            }
        }
    }
    
    static function createDoc($model,$data){
        if (!file_exists($model)){
            return Array("success"=>-1,"message"=>"Il File $model non è stato trovato");
        }
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
        $TBS->LoadTemplate($model);
        $TBS->SetOption('noerr',true);
        array_walk_recursive($data, 'decode');
        self::mergeFields($TBS,$data);
        $TBS->PlugIn(OPENTBS_SELECT_HEADER);
        self::mergeFields($TBS,$data);
        $TBS->PlugIn(OPENTBS_SELECT_FOOTER);
        self::mergeFields($TBS,$data);
        $TBS->Show(OPENTBS_STRING);
        return Array("success"=>1,"message"=>"","file"=>$TBS->Source);
    }
    static function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
        $chars_length = (strlen($chars) - 1);
        $string = $chars{rand(0, $chars_length)};
        for ($i = 1; $i < $length; $i = strlen($string)){
            $r = $chars{rand(0, $chars_length)};
            if ($r != $string{$i - 1}) $string .=  $r;
        }
        return $string;
    }
    static function writeFile($text,$ext,$base64=false,$name="",$dir="/tmp/"){
        $nome=($name)?($name):(self::rand_str());
        $nomefile=($ext)?(sprintf("%s.%s",$nome,$ext)):($nome);
        $f=fopen($dir.$nomefile,'w');
        $text=($base64)?(base64_decode($text,true)):($text);
        if(fwrite($f, $text)){
            fclose($f);
            return Array("success"=>1,"message"=>"","file"=>$dir.$nomefile);
        }
        else{
            return Array("success"=>0,"message"=>"Impossibile scrivere il file","file"=>"");
        }
    }
    static function convertToPdf($file){
        if (!file_exists($file)){
            return Array("success"=>-1,"message"=>"Il file $fName non esiste","file"=>"");
        }
        $fileInfo=  pathinfo($file);
        $cmd=CMD_DIR."soffice  --headless --invisible --nologo --convert-to pdf ".escapeshellarg($file)." --outdir ".TMP_DIR;
        echo $cmd;
        $res=exec($cmd);
	$msg1="Overwriting:";// $dirname/$filename";
	$msg2="convert";// $dirname/$filename";
	if (stripos($res,$msg1)===FALSE and stripos($res,$msg2)===FALSE){
            return Array("success"=>-1,"message"=>$res,"file"=>"");
        }
        $fName=TMP_DIR.$fileInfo["filename"].".pdf";
        if (file_exists($fName)){
            $f=fopen($fName,'r');
            $text=fread($f,filesize($fName));
            fclose($f);
            unlink($fName);
            return Array("success"=>1,"message"=>"","file"=>$text);
        }
        else{
            return Array("success"=>-1,"message"=>"Il file $fName non esiste","file"=>"");
        }
    }
}
