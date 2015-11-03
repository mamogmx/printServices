<?php
error_reporting(E_ALL);
require_once "../config.php";

$data = Array(
	"nominativo"=>"Marco Carbone",
	"data"=>date("d/m/Y H:i:s"),
	"altri_nominativi"=>Array(
		"Claudio Tosi",
		"Silvia Tavlaridis",
		"Vito Labbe",
		"Davide Caviglia"
	)
);


$p = new printDoc("docx");

$templates=Array(
	"normativa_prg"=>Array(
		BASE_PATH."modelli/alghero/cdu/normativa/Zona B3.docx",
		BASE_PATH."modelli/alghero/cdu/normativa/Zona SQ.docx",
		BASE_PATH."modelli/alghero/cdu/normativa/Zona G8.docx"
	),
	"testo"=>BASE_PATH."modelli/template-1.docx"
);
$p->loadData(json_encode($data));
$p->loadAllDocx($templates);
//$p->testWriting();

if($p->loadModel("filesystem", "modello-test-1.docx")===FALSE){
	echo "ERROR";
}

$p->loadData(json_encode($data));
//echo "<pre>";print_r($p);echo "</pre>";
$p->createDocument($p->tmpDir."test-stampa-1.docx");
if (file_exists($p->tmpDir."test-stampa-1.docx"))	
	echo "<a href=\"../tmp/test-stampa-1.docx\" target=\"_new\">Documento Generato il ".date("d/m/Y")." alle ".date("H:i:s")."</a>";
else 
	echo "<p>Errore nella creazione del documento</p>";



?>