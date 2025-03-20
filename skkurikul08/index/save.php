<?php
session_start();
include '../dbasse_conn.php';

$user_ID = 1024;/*$_SESSION['user_ID'];*/
$aktivnost_ID = $_POST['aktivnost_ID'];

//if isset = true then $_POST[] else null
$opciPodaci = isset($_POST['opciPodaci']) ? $_POST['opciPodaci'] : null;
$nositelji = isset($_POST['nositelji']) ? $_POST['nositelji'] : null;
$ciljevi = isset($_POST['ciljevi']) ? $_POST['ciljevi'] : null;
$troskovnik = isset($_POST['troskovnik']) ? $_POST['troskovnik'] : null;
$vrednovanja = isset($_POST['vrednovanje']) ? $_POST['vrednovanje'] : null;
$realizacije = isset($_POST['realizacije']) ? $_POST['realizacije'] : null;

$data = [
    'user_ID' => $user_ID ?? 'null',
    'aktivnost_ID' => $aktivnost_ID ?? 'null',
    'opciPodaci' => $opciPodaci ?? 'null',
    'nositelji' => $nositelji ?? 'null',
    'ciljevi' => $ciljevi ?? 'null',
    'troskovnik' => $troskovnik ?? 'null',
    'vrednovanje' => $vrednovanje ?? 'null',
    'realizacije' => $realizacije ?? 'null'
];

//za debug i saki slucaj
//$content = print_r($data, true);
//file_put_contents('log.txt', $content, FILE_APPEND);


//provjera prava
$sql = "SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM sk_prava
            WHERE KorisnikID = ? 
            AND AktivnostID = ? 
            AND PravoID IN (1, 2)
        ) THEN 1
        ELSE 0
    END AS imaPravo";


$pravo = fetchSingleResult($con, $sql, [$user_ID, $aktivnost_ID]);

if($pravo['imaPravo'] == 0){
    $status["pravo"] = "Nemate pravo uređivanja!";
    echo json_encode($status, JSON_UNESCAPED_UNICODE);
    exit();

}

//opci podaci 1
$sql = "UPDATE sk_aktivnost 
SET Naziv = (SELECT ?), 
    Namjena = (SELECT ?), 
    Vremenik = (SELECT ?), 
    Datum = (SELECT STR_TO_DATE(?, '%Y-%m-%d')) 
WHERE ID = ?";

$parms = [$opciPodaci["name"], $opciPodaci["purpose"], $opciPodaci["timeline"], $opciPodaci["created"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

//-----------------------
//-----------------------
//-----------------------
//-----------------------
//to se nozda mre skupa zapisati !!!!
//-----------------------
//-----------------------
//-----------------------
//-----------------------

//opci podaci - vrsta aktivnosti 
$sql = "UPDATE sk_aktivnosti 
SET VrstaID = (SELECT ID FROM sk_vrsteaktivnosti WHERE ID = ? AND Aktivno = 1) 
WHERE AktivnostID = ?";

$parms = [$opciPodaci["activity-type"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

//opci podaci - status
$sql = "UPDATE sk_aktivnosti 
SET StatusID = (SELECT ID FROM sk_statusi WHERE ID = ?) 
WHERE AktivnostID = ?;";

$parms = [$opciPodaci["status"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);


//opci podaci - izvjestaj
$sql = "SELECT izvjesceID FROM sk_aktivnosti WHERE AktivnostID = ?";
$row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
$izvjesce_ID = $row['izvjesceID'];

if($izvjesce_ID === -1 || $izvjesce_ID === NULL){
    // Insert new record into sk_izvjesce
    $sql = "INSERT INTO sk_izvjesce (opis, potvrdeno, potvrdenoAdminID, generiranaPotvrda, urBroj, kategorijaNapredovanja)
    VALUES (?, 0, -1, 0, -1, 1)";
    $parms = [$opciPodaci["report"]];
    placeToDataBase($con, $sql, $parms);

    // Get last inserted ID
    $sql = "SELECT LAST_INSERT_ID() as last_id";
    $row = fetchSingleResult($con, $sql, []);
    
    if ($row && isset($row['last_id'])) {
        $lastInsertId = $row['last_id'];

        // Update sk_aktivnosti with the new izvjesceID
        $sql = "UPDATE sk_aktivnosti SET izvjesceID = ? WHERE AktivnostID = ?;";
        $parms = [$lastInsertId, $aktivnost_ID];
        placeToDataBase($con, $sql, $parms);
    } else {
        file_put_contents('log.txt', "Error: Could not retrieve last insert ID\n", FILE_APPEND);
    }
} else {
    // Update existing record in sk_izvjesce
    $sql = "UPDATE sk_izvjesce SET opis = ? WHERE id = ?";
    $parms = [$opciPodaci["report"], $izvjesce_ID];
    placeToDataBase($con, $sql, $parms);
}


//za nositelje
$aktivnost_ID = $_POST['aktivnost_ID'];

$nositelji = $_POST['nositelji'] ?? [];
if (empty($nositelji)) {
    exit; // Exit if no data is provided
}

// Fetch all current nositelji for the given AktivnostID
$currentNositelji = fetchMultipleResults($con, "SELECT KorisnikID FROM sk_nositelji WHERE AktivnostID = ?", [$aktivnost_ID]);

$currentNositeljiArray = array_column($currentNositelji, 'KorisnikID');


// Determine new, existing, and removed nositelji
$toAdd = array_diff($nositelji, $currentNositeljiArray);
$toUpdate = array_intersect($nositelji, $currentNositeljiArray);
$toDeactivate = array_diff($currentNositeljiArray, $nositelji);

// Insert new nositelji
foreach ($toAdd as $korisnikID) {
    placeToDataBase($con, "INSERT INTO sk_nositelji (AktivnostID, KorisnikID, Aktivno) VALUES (?, ?, 1)", [$aktivnost_ID, $korisnikID]);
}

// Update existing nositelji to aktivno = 1
foreach ($toUpdate as $korisnikID) {
    placeToDataBase($con, "UPDATE sk_nositelji SET Aktivno = 1 WHERE AktivnostID = ? AND KorisnikID = ?", [$aktivnost_ID, $korisnikID]);
}

// Deactivate removed nositelji (set aktivno = 0)
foreach ($toDeactivate as $korisnikID) {
    placeToDataBase($con, "UPDATE sk_nositelji SET Aktivno = 0 WHERE AktivnostID = ? AND KorisnikID = ?", [$aktivnost_ID, $korisnikID]);
}


// ---------------------------
// CILJEVI
// ---------------------------
//prvo postavi sve koji nisu na web_arrayu a jesu na db_arrayu na obirsano = 1, 
//nakon toga dodajem nove; jer oni tak tad dobivaju ID pa da posljed brisem onda bi i njih posavil u obrisano


$content = print_r($ciljevi, true);
file_put_contents('log.txt', $content, FILE_APPEND);

if (!isset($ciljevi) || !is_array($ciljevi)) {
    $ciljevi = [];
}


$sql = "SELECT ID FROM sk_ciljevi WHERE AktivnostID = ? AND Obrisano = 0;";
$ciljevi_db = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

// Extract IDs from the $ciljevi array using the uppercase key
$ciljevi_ids = array_column($ciljevi, 'ID');

foreach ($ciljevi_db as $cilj_db) {
    // Check using uppercase 'ID'
    if (!in_array($cilj_db['ID'], $ciljevi_ids)) {
        $sql = "UPDATE sk_ciljevi SET Obrisano = 1 WHERE ID = ?";
        $parms = [$cilj_db['ID']];
        placeToDataBase($con, $sql, $parms);
    }
}


if (!is_null($ciljevi)) { 
    foreach ($ciljevi as $cilj) {
        // Check using uppercase 'ID'
        if ($cilj['ID'] == 0) { // Use loose comparison (==) if types might differ
            $sql = "INSERT INTO sk_ciljevi (AktivnostID, Cilj, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $cilj['cilj']];
            placeToDataBase($con, $sql, $parms);
        } else {
            $parms = [$cilj['cilj'], $cilj['ID']];
            $sql = "UPDATE sk_ciljevi SET Cilj = ? WHERE ID = ?";
            placeToDataBase($con, $sql, $parms);
        }
    }
}

// ---------------------------
// REALIZACIJE
// ---------------------------

if (!isset($realizacije) || !is_array($realizacije)) {
    $realizacije = [];
}

$content = print_r($realizacije, true);
file_put_contents('log.txt', $content, FILE_APPEND);


$sql = "SELECT ID FROM sk_realizacije WHERE AktivnostID = ? AND Obrisano = 0;";
$realizacije_db = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

// Extract IDs from the $realizacije array using the uppercase key
$realizacije_ids = array_column($realizacije, 'ID');

foreach ($realizacije_db as $realizacija_db) {
    // Check using uppercase 'ID'
    if (!in_array($realizacija_db['ID'], $realizacije_ids)) {
        $sql = "UPDATE sk_realizacije SET Obrisano = 1 WHERE ID = ?";
        $parms = [$realizacija_db['ID']];
        placeToDataBase($con, $sql, $parms);
    }
}


if (!is_null($realizacije)) { 
    foreach ($realizacije as $realizacija) {
        // Check using uppercase 'ID'
        if ($realizacija['ID'] == 0) { // Use loose comparison (==) if types might differ
            $sql = "INSERT INTO sk_realizacije (AktivnostID, Realizacija, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $realizacija['realizacija']];
            placeToDataBase($con, $sql, $parms);
        } else {
            $parms = [$realizacija['realizacija'], $realizacija['ID']];
            $sql = "UPDATE sk_realizacije SET Realizacija = ? WHERE ID = ?";
            placeToDataBase($con, $sql, $parms);
        }
    }
}

// ---------------------------
// VREDNOVANJA
// ---------------------------

if (!isset($vrednovanja) || !is_array($vrednovanja)) {
    $vrednovanja = [];
}
$sql = "SELECT ID FROM sk_vrednovanja WHERE AktivnostID = ? AND Obrisano = 0;";
$vrednovanja_db = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

// Extract IDs from the $vrednovanja array using the uppercase key
$vrednovanja_ids = array_column($vrednovanja, 'ID');

foreach ($vrednovanja_db as $vrednovanje_db) {
    // Check using uppercase 'ID'
    if (!in_array($vrednovanje_db['ID'], $vrednovanja_ids)) {
        $sql = "UPDATE sk_vrednovanja SET Obrisano = 1 WHERE ID = ?";
        $parms = [$vrednovanje_db['ID']];
        placeToDataBase($con, $sql, $parms);
    }
}

if (!is_null($vrednovanja)) { 
    foreach ($vrednovanja as $vrednovanje) {
        // Check using uppercase 'ID'
        if ($vrednovanje['ID'] == 0) { // Use loose comparison (==) if types might differ
            $sql = "INSERT INTO sk_vrednovanja (AktivnostID, Vrednovanje, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $vrednovanje['vrednovanje']];
            placeToDataBase($con, $sql, $parms);
        } else {
            $parms = [$vrednovanje['vrednovanje'], $vrednovanje['ID']];
            $sql = "UPDATE sk_vrednovanja SET Vrednovanje = ? WHERE ID = ?";
            placeToDataBase($con, $sql, $parms);
        }
    }
}

// ---------------------------
// TROSKOVNIK
// ---------------------------
if (!isset($troskovnik) || !is_array($troskovnik)) {
    $troskovnik = [];
}

$sql = "SELECT ID FROM sk_troskovnik WHERE AktivnostID = ? AND Obrisano = 0;";
$troskovnik_db = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

// If $troskovnik is empty, this returns an empty array.
$troskovnik_ids = !empty($troskovnik) ? array_column($troskovnik, 'ID') : [];

foreach ($troskovnik_db as $trosak_db) {
    // If $troskovnik_ids is empty, in_array() will always return false.
    if (!in_array($trosak_db['ID'], $troskovnik_ids)) {
        $sql = "UPDATE sk_troskovnik SET Obrisano = 1 WHERE ID = ?";
        $parms = [$trosak_db['ID']];
        placeToDataBase($con, $sql, $parms);
    }
}

if (!empty($troskovnik)) { 
    foreach ($troskovnik as $trosak) {
        // Check using uppercase 'ID'
        if ($trosak['ID'] == 0) { // Use loose comparison (==) if types might differ
            $sql = "INSERT INTO sk_troskovnik (AktivnostID, Trosak, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $trosak['trosak']];
            placeToDataBase($con, $sql, $parms);
        } else {
            $parms = [$trosak['trosak'], $trosak['ID']];
            $sql = "UPDATE sk_troskovnik SET Trosak = ? WHERE ID = ?";
            placeToDataBase($con, $sql, $parms);
        }
    }
}


$status["pravo"] = "Spemanje uspjesno!";
echo json_encode($status, JSON_UNESCAPED_UNICODE);



?>