<?php
include '../dbasse_local.php';
//daje sve aktivnosti za pojedninog korisnika

$user_ID = $_GET["user_ID"];
$vrstaAktivnosti_ID = $_GET["odabir"];

if($vrstaAktivnosti_ID != -1){
        $sql = "SELECT DISTINCT sk_Aktivnost.Naziv, sk_Aktivnost.ID 
        FROM sk_Aktivnost 
        JOIN sk_Prava ON sk_Aktivnost.ID = sk_Prava.AktivnostID
        JOIN sk_Aktivnosti ON sk_Aktivnost.ID = sk_Aktivnosti.AktivnostID
        WHERE sk_Prava.KorisnikID = ? AND sk_Aktivnosti.VrstaID = ? AND Aktivno = 1;";

$aktivnosti_array = fetchMultipleResults($con,$sql,[$user_ID,$vrstaAktivnosti_ID]);
}

else{
        $sql = "SELECT DISTINCT sk_Aktivnost.Naziv, sk_Aktivnost.ID 
        FROM sk_Aktivnost 
        JOIN sk_Prava ON sk_Aktivnost.ID = sk_Prava.AktivnostID
        WHERE sk_Prava.KorisnikID = ? AND Aktivno = 1;";
        $aktivnosti_array = fetchMultipleResults($con,$sql,[$user_ID]); 
}



echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
?>