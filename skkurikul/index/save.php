<?php
session_start();
include '../dbasse_conn.php';

$user_ID = 1024;/*$_SESSION['user_ID'];*/
$aktivnost_ID = 1168;//$_POST['aktivnost_ID'];

//if isset = true then $_POST[] else null
$opciPodaci = isset($_POST['opciPodaci']) ? $_POST['opciPodaci'] : null;
$nositelji = isset($_POST['nositelji']) ? $_POST['nositelji'] : null;
$ciljevi = isset($_POST['ciljevi']) ? $_POST['ciljevi'] : null;
$troskovnik = isset($_POST['troskovnik']) ? $_POST['troskovnik'] : null;
$vrednovanje = isset($_POST['vrednovanje']) ? $_POST['vrednovanje'] : null;
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
$content = print_r($data, true);
file_put_contents('log.txt', $content, FILE_APPEND);


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
$aktivnost_ID = 1168;//$_POST['aktivnost_ID'];
$nositelji = isset($_POST['nositelji']) ? (is_array($_POST['nositelji']) ? $_POST['nositelji'] : explode(',', $_POST['nositelji'])) : [];

if (empty($nositelji)) {
    exit; // Exit if no data is provided
}

// Fetch all current nositelji for the given AktivnostID
$currentNositelji = fetchMultipleResults($con, "SELECT KorisnikID FROM sk_nositelji WHERE AktivnostID = ?", [$aktivnost_ID]);

$currentNositeljiArray = array_column($currentNositelji, 'KorisnikID');

// Debugging output
// var_dump($nositelji, $currentNositeljiArray);
// exit;

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



$status["pravo"] = "Spemanje uspjesno!";

//debug
//file_put_contents('log.txt', print_r($status, true), FILE_APPEND);

echo json_encode($status, JSON_UNESCAPED_UNICODE);


?>
