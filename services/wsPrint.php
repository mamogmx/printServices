<?php
require_once "../config.php";
require_once LIB_DIR."nusoap/nusoap.php";
$serviceUrl = sprintf("http%s://%s%s?wsdl",(!empty($_SERVER['HTTPS'])?"s":""),$_SERVER['SERVER_NAME'],$_SERVER['SCRIPT_NAME']);
$server = new nusoap_server; 
$server->soap_defencoding = 'UTF-8';
$server->configureWSDL('printservice', $serviceUrl);
$server->register('createDocument',
    Array(
        "data"=>"xsd:string",
        "file"=>"xsd:string",
        "validation"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "file"=>"xxsd:string",
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
        "file"=>"xxsd:string",
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
        "file"=>"xxsd:string",
        "message"=>"xsd:string"
    ),
    'urn:printservice',
    'urn:printservice#mergeConvertDocument',
    'rpc',
    'encoded',
    'Metodo che dato un modello in Docx/Odt restituisce un file con il merge dei dati nel modello convertito in Pdf'
);

function createDocument($data,$file,$validation){
    
}
function convertDocument($data,$file,$validation){
    
}
function mergeConvertDocument($data,$file,$validation){
    
}
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
