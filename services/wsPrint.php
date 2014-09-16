<?php
require_once "../config.php";
$serviceUrl = sprintf("http%s://%s%s?wsdl",(!empty($_SERVER['HTTPS'])?"s":""),$_SERVER['SERVER_NAME'],$_SERVER['SCRIPT_NAME']);
$server = new nusoap_server; 
$server->soap_defencoding = 'UTF-8';
$server->configureWSDL('printservice', $serviceUrl);
$server->register('createDocument',
    Array(
        "data"=>"xsd:string",
        "file"=>"xsd:string",
        "ext"=>"xsd:string",
        "validation"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "file"=>"xsd:string",
        "message"=>"xsd:string"
    ),
    'urn:printservice',
    'urn:printservice#createDocument',
    'rpc',
    'encoded',
    'Metodo che dato un modello in Docx/Odt restituisce un file con il merge dei dati nel modello'
);
$server->register('convertDocument',
    Array(
        "file"=>"xsd:string",
        "validation"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "file"=>"xsd:string",
        "message"=>"xsd:string"
    ),
    'urn:printservice',
    'urn:printservice#convertDocument',
    'rpc',
    'encoded',
    'Metodo che dato un documento in Docx/Odt restituisce il file convertito in Pdf'
);
$server->register('mergeConvertDocument',
    Array(
        "data"=>"xsd:string",
        "file"=>"xsd:string",
        "validation"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "file"=>"xsd:string",
        "message"=>"xsd:string"
    ),
    'urn:printservice',
    'urn:printservice#mergeConvertDocument',
    'rpc',
    'encoded',
    'Metodo che dato un modello in Docx/Odt restituisce un file con il merge dei dati nel modello convertito in Pdf'
);

function createDocument($data,$file,$ext,$validation){
    if (!utils::checkValidation($validation)) {
        return Array("success"=>0,"file"=>"","message"=>"Non si dispone dell autorizzazioni per effettuare l'operazione");
    }
    $res=  utilsPrint::decodeData($data);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $data=$res["result"];
    $res = utilsPrint::writeFile($file, $ext, null,TMP_DIR);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $res = utilsPrint::createDoc($res["file"], $data);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    else{
        return Array("success"=>1,"message"=>"","file"=>  base64_encode($res["file"]));
    }
}
function convertDocument($file,$validation){
    if (!utils::checkValidation($validation)) {
        return Array("success"=>0,"file"=>"","message"=>"Non si dispone dell autorizzazioni per effettuare l'operazione");
    }
    $res = utilsPrint::writeFile($file, null, null,TMP_DIR);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $res = utilsPrint::convertToPdf($res["file"]);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    else{
        return Array("success"=>1,"message"=>"","file"=>  base64_encode($res["file"]));
    }
}
function mergeConvertDocument($data,$file,$validation){
    if (!utils::checkValidation($validation)) {
        return Array("success"=>0,"file"=>"","message"=>"Non si dispone dell autorizzazioni per effettuare l'operazione");
    }
    $res=  utilsPrint::decodeData($data);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $data=$res["result"];
    $res = utilsPrint::writeFile($file, $ext, null,TMP_DIR);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $res = utilsPrint::createDoc($res["file"], $data);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $res = utilsPrint::writeFile($res["file"], $ext, null,TMP_DIR);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    $res = utilsPrint::convertToPdf($res["file"]);
    if ($res["success"]!=1){
        return Array("success"=>0,"file"=>"","message"=>$res["message"]);
    }
    else{
        return Array("success"=>1,"message"=>"","file"=>  base64_encode($res["file"]));
    }
}
$HTTP_RAW_POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
$server->service($HTTP_RAW_POST_DATA);
exit();
?>
