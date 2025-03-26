<?php
include '../dbasse_conn.php';
$aktivnost_ID = $_GET['aktivnost_ID'];

$opci_array = [];

//fetchSingleResult($con,$sql, $params)

//Naziv, Vremenik, Datum, Namjena
$sql_querry[] = "SELECT Naziv, Vremenik, Datum AS Kreirano, Namjena FROM sk_aktivnost WHERE ID = ? AND Obrisano = 0;";

// AUTOR
$sql_querry[] = "SELECT sk_korisnici.FullName AS Autor FROM sk_aktivnosti JOIN sk_korisnici ON sk_aktivnosti.AutorID = sk_korisnici.ID WHERE sk_aktivnosti.AktivnostID = ?";

// VRSTA AKTIVNOSTI
$sql_querry[] = "SELECT sk_vrsteaktivnosti.ID AS Vrsta_Aktivnosti FROM sk_aktivnosti JOIN sk_vrsteaktivnosti ON sk_aktivnosti.VrstaID = sk_vrsteaktivnosti.ID WHERE sk_aktivnosti.AktivnostID = ? AND sk_vrsteaktivnosti.Aktivno = 1";

// STATUS
$sql_querry[] = "SELECT sk_statusi.ID AS Status FROM sk_aktivnosti JOIN sk_statusi ON sk_aktivnosti.StatusID = sk_statusi.ID WHERE sk_aktivnosti.AktivnostID = ?";

// IZVJESCE
$sql_querry[] = "SELECT opis AS Izvjesce FROM sk_izvjesce JOIN sk_aktivnosti ON sk_aktivnosti.izvjesceID = sk_izvjesce.id WHERE sk_aktivnosti.AktivnostID = ?";

foreach ($sql_querry as $sql) {
    $row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
    if ($row) {
        $opci_array = array_merge($opci_array, $row);
    }
}

$return_array = array_fill(0, 6, []);
$return_array[0] = $opci_array;

//fetchMultipleResults($con,$sql, $params) 
// CILJEVI
$sql_querry_multiple[] = "SELECT sk_ciljevi.ID,Cilj FROM sk_ciljevi WHERE AktivnostID = ? AND Obrisano = 0";

// TROSKOVNIK
$sql_querry_multiple[] = "SELECT sk_troskovnik.ID,Trosak FROM sk_troskovnik WHERE AktivnostID = ? AND Obrisano = 0";

// REALIZACIJA
$sql_querry_multiple[] = "SELECT sk_realizacije.ID,Realizacija FROM sk_realizacije WHERE AktivnostID = ? AND Obrisano = 0";

// NACIN VREDNOVANJA
$sql_querry_multiple[] = "SELECT sk_vrednovanja.ID,Vrednovanje FROM sk_vrednovanja WHERE AktivnostID = ? AND Obrisano = 0";

// NOSITELJI
$sql_querry_multiple[] = "SELECT sk_korisnici.ID FROM sk_korisnici JOIN sk_nositelji ON sk_nositelji.KorisnikID = sk_korisnici.ID WHERE sk_nositelji.AktivnostID = ? AND sk_nositelji.Aktivno = 1";



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
