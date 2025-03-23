<?php
include '../dbasse_conn.php';
//daje sve aktivnosti za pojedninog korisnika

$user_ID = $_POST["user_ID"];
$vrstaAktivnosti_ID = $_POST["odabir"];
$aktualnaGodina = $_POST["aktualnaGodina"];

if ($user_ID == 1) {
        $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID WHERE sk_aktivnost.Obrisano = 0;";
        $parms = [];
} else {
        if ($vrstaAktivnosti_ID != -1) {
                $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
                WHERE sk_nositelji.KorisnikID = ? 
                AND sk_aktivnosti.VrstaID = ? 
                AND sk_aktivnost.Obrisano = 0;";
                $parms = [$user_ID, $vrstaAktivnosti_ID];
        } else {
                $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
                WHERE sk_nositelji.KorisnikID = ? 
                AND sk_aktivnost.Obrisano = 0;";
                $parms = [$user_ID];
        }
}
/*
$godina = date("Y");
if($aktualnaGodina){
        $parms[] = $godina;
        $sql.= " AND sk_aktivnosti.skolskaGodina = ?";    
}
*/
$aktivnosti_array = fetchMultipleResults($con, $sql, $parms);
echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
