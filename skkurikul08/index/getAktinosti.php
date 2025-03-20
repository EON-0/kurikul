<?php
include '../dbasse_conn.php';
//daje sve aktivnosti za pojedninog korisnika
 
$user_ID = $_POST["user_ID"];
$vrstaAktivnosti_ID = $_POST["odabir"];
$aktualnaGodina = $_POST["aktualnaGodina"];

if($vrstaAktivnosti_ID != -1){
        $sql = "SELECT DISTINCT sk_aktivnost.Naziv, sk_aktivnost.ID 
        FROM sk_aktivnost 
        JOIN sk_prava ON sk_aktivnost.ID = sk_Prava.AktivnostID
        JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
        WHERE sk_prava.KorisnikID = ? AND sk_aktivnosti.VrstaID = ? AND Aktivno = 1;";

$aktivnosti_array = fetchMultipleResults($con,$sql,[$user_ID,$vrstaAktivnosti_ID]);
}
else{
        $sql = "SELECT DISTINCT sk_aktivnost.Naziv, sk_aktivnost.ID 
        FROM sk_aktivnost 
        JOIN sk_prava ON sk_aktivnost.ID = sk_prava.AktivnostID
        WHERE sk_prava.KorisnikID = ? AND Aktivno = 1;";
        $aktivnosti_array = fetchMultipleResults($con,$sql,[$user_ID]); 
}

echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
?>