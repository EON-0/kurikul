<?php
include '../dbasse_local.php';
//daje sve aktivnosti za pojedninog klorisnika
$user_ID = $_GET["user_ID"];

$sql = "SELECT DISTINCT sk_Aktivnost.Naziv, sk_Aktivnost.ID 
        FROM sk_Aktivnost 
        JOIN sk_Prava ON sk_Aktivnost.ID = sk_Prava.AktivnostID 
        WHERE sk_Prava.KorisnikID = ? AND Aktivno = 1;";

$aktivnosti_array = fetchMultipleResults($con,$sql,[$user_ID]);

echo json_encode($aktivnosti_array);


?>