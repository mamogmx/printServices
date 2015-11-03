<?php
error_reporting(E_ALL);
require_once "../config.php";
echo date("d/m/Y H:i:s");
require_once LIB_DIR.'phpDocx/classes/TransformDoc.inc';

$document = new TransformDoc();
echo "<pre>";print_r($document);echo "</pre>";
$document->setStrFile('../tmp/test-stampa-1.docx');

$document->generatePDF();
?>