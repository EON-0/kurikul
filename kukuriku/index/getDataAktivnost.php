<?php
include '../dbasse_conn.php';
$aktivnost_ID = $_GET['aktivnost_ID'];

//treba sloziti provjeru ako user ima pristup toj aktivnosti, kaj nabudu mogli promjeniti 

$opci_array = [];

//fetchSingleResult($con,$sql, $params)

//Naziv, Vremenik, Datum, Namjena
$sql_querry[] = "SELECT Naziv, Vremenik, Datum AS Kreirano, Namjena FROM sk_Aktivnost WHERE ID = ? AND Obrisano = 0;";

// AUTOR
$sql_querry[] = "SELECT sk_Korisnici.FullName AS Autor FROM sk_Aktivnosti JOIN sk_Korisnici ON sk_Aktivnosti.AutorID = sk_Korisnici.ID WHERE sk_Aktivnosti.AktivnostID = ?";

// VRSTA AKTIVNOSTI
$sql_querry[] = "SELECT sk_VrsteAktivnosti.ID AS Vrsta_Aktivnosti FROM sk_Aktivnosti JOIN sk_VrsteAktivnosti ON sk_Aktivnosti.VrstaID = sk_VrsteAktivnosti.ID WHERE sk_Aktivnosti.AktivnostID = ? AND sk_VrsteAktivnosti.Aktivno = 1";

// STATUS
$sql_querry[] = "SELECT sk_Statusi.ID AS Status FROM sk_Aktivnosti JOIN sk_Statusi ON sk_Aktivnosti.StatusID = sk_Statusi.ID WHERE sk_Aktivnosti.AktivnostID = ?";

// IZVJESCE
$sql_querry[] = "SELECT opis AS Izvjesce FROM sk_Izvjesce JOIN sk_Aktivnosti ON sk_Aktivnosti.izvjesceID = sk_Izvjesce.id WHERE sk_Aktivnosti.AktivnostID = ? AND sk_Izvjesce.potvrdeno = 1";

foreach ($sql_querry as $sql) {
    $row = fetchSingleResult($con,$sql,[$aktivnost_ID]);
    if($row){
        $opci_array = array_merge($opci_array,$row);
    }
}

$return_array = array_fill(0, 6, []);
$return_array[0] = $opci_array;

//fetchMultipleResults($con,$sql, $params) 
// CILJEVI
$sql_querry_multiple[] = "SELECT sk_Ciljevi.ID,Cilj FROM sk_Ciljevi WHERE AktivnostID = ? AND Obrisano = 0";

// TROSKOVNIK
$sql_querry_multiple[] = "SELECT sk_Troskovnik.ID,Trosak FROM sk_Troskovnik WHERE AktivnostID = ? AND Obrisano = 0";

// REALIZACIJA
$sql_querry_multiple[] = "SELECT sk_Realizacije.ID,Realizacija FROM sk_Realizacije WHERE AktivnostID = ? AND Obrisano = 0";

// NACIN VREDNOVANJA
$sql_querry_multiple[] = "SELECT sk_Vrednovanja.ID,Vrednovanje FROM sk_Vrednovanja WHERE AktivnostID = ? AND Obrisano = 0";

// NOSITELJI
$sql_querry_multiple[] = "SELECT sk_korisnici.ID FROM sk_korisnici JOIN sk_nositelji ON sk_nositelji.KorisnikID = sk_korisnici.ID WHERE sk_nositelji.AktivnostID = ?";


foreach ($sql_querry_multiple as $index => $sql) {
    $row = fetchMultipleResults($con, $sql, [$aktivnost_ID]);
    $return_array[$index + 1] = $row ?: [];
}


// 0 -> opcenito +
// 1 -> ciljevi +
// 2 -> troskovnik +
// 3 -> realizacija +
// 4 -> nacin vrednovanja +
// 5 -> nositelji +
echo json_encode($return_array, JSON_UNESCAPED_UNICODE);



?>