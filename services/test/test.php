<?php
require_once "../../config.php";

$data = Array(
	"nominativo"=>"Marco Carbone",
	"altri_nominativi"=>Array(
		"Claudio Tosi",
		"Silvia Tavlaridis",
		"Vito Labbe"
	)
);

$p = new printDoc("docx");
$p->loadModel("filesystem", "modello-test-1.docx");
$p->loadData(json_encode($data));
$p->createDocument("test-stampa-1.docx");
?>