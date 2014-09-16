<?php
$r=$data["azienda_richiedente_ateco"];
for($i=0;$i<count($r);$i++){
    $res[$i]["ateco"]=$r[$i];
}
$data["azienda_richiedente_ateco"]=$res;

?>