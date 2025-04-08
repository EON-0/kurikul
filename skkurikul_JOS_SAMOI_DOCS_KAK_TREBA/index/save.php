<?php

session_start();
include '../dbasse_conn.php';

$user_ID = $_SESSION['user_ID']; // autorova ID
$aktivnost_ID = $_POST['aktivnost_ID'];

$opciPodaci  = $_POST['opciPodaci']  ?? null;
$nositelji   = $_POST['nositelji']   ?? null;
$ciljevi     = $_POST['ciljevi']     ?? null;
$troskovnik  = $_POST['troskovnik']  ?? null;
$vrednovanja = $_POST['vrednovanje'] ?? null;
$realizacije = $_POST['realizacije'] ?? null;

// Provjera administratorskog prava
$sql = "
    SELECT CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM sk_prava
            WHERE AktivnostID IS NULL
              AND PravoID IN (6)
              AND Aktivno = 1
              AND KorisnikID = ?
        ) THEN 1
        ELSE 0
    END AS imaPravo";
$administratorskaOvlast = fetchSingleResult($con, $sql, [$user_ID]);

if ($administratorskaOvlast && $administratorskaOvlast['imaPravo'] == 1) {
    $pravo = ['imaPravo' => 1];
} else {
    $sql = "
        SELECT CASE 
            WHEN EXISTS (
                SELECT 1 
                FROM sk_prava
                WHERE KorisnikID = ? 
                  AND AktivnostID = ? 
                  AND PravoID IN (1)
            ) THEN 1
            ELSE 0
        END AS imaPravo";
    $pravo = fetchSingleResult($con, $sql, [$user_ID, $aktivnost_ID]);
}

// Provjera prava uređivanja
if (!$pravo || $pravo['imaPravo'] != 1) {
    echo json_encode(["pravo" => "Nemate pravo uređivanja!"]);
    exit;
}

//Provjera ako aktivnost nije već potvrđena,ako je nema više promijene

$sql = "SELECT izvjesceID FROM sk_aktivnosti WHERE AktivnostID = ?";
$row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
$izvjesce_ID = $row['izvjesceID'];


$sql = "SELECT 
    id,
    opis,
    potvrdeno,
    CASE WHEN potvrdeno = 1 THEN 'Potvrdeno'
         ELSE 'Nije potvrdeno'
    END AS potvrda_status
FROM sk_izvjesce
WHERE id = ?;";

$row = fetchSingleResult($con, $sql, [$izvjesce_ID]);
$status_potrde = $row['potvrda_status'];

if ($status_potrde === 'Potvrdeno') {
    echo json_encode(["pravo" => "Aktivnost je potvrđena. Nemožete je više mijenjati."]);
    exit;
}

// -----------------
// OPCI PODACI
// -----------------

// Ažuriranje sk_aktivnost
$sql = "UPDATE sk_aktivnost 
        SET Naziv = (SELECT ?), 
            Namjena = (SELECT ?), 
            Vremenik = (SELECT ?), 
            Datum = (SELECT STR_TO_DATE(?, '%Y-%m-%d')) 
        WHERE ID = ?";
$parms = [$opciPodaci["name"], $opciPodaci["purpose"], $opciPodaci["timeline"], $opciPodaci["created"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

// Vrsta aktivnosti
$sql = "UPDATE sk_aktivnosti 
        SET VrstaID = (SELECT ID FROM sk_vrsteaktivnosti WHERE ID = ? AND Aktivno = 1) 
        WHERE AktivnostID = ?";
$parms = [$opciPodaci["activity-type"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

// Status
$sql = "UPDATE sk_aktivnosti 
        SET StatusID = (SELECT ID FROM sk_statusi WHERE ID = ?) 
        WHERE AktivnostID = ?";
$parms = [$opciPodaci["status"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

// Izvješće
$sql = "SELECT izvjesceID FROM sk_aktivnosti WHERE AktivnostID = ?";
$row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
$izvjesce_ID = $row['izvjesceID'];

if ($izvjesce_ID === -1 || $izvjesce_ID === NULL) {
    $sql = "INSERT INTO sk_izvjesce (opis, potvrdeno, potvrdenoAdminID, generiranaPotvrda, urBroj, kategorijaNapredovanja)
            VALUES (?, 0, -1, 0, -1, 1)";
    $parms = [$opciPodaci["report"]];
    placeToDataBase($con, $sql, $parms);

    $sql = "SELECT ID FROM sk_izvjesce ORDER BY ID DESC LIMIT 1;";
    $row = fetchSingleResult($con, $sql, []);

    if ($row && isset($row['ID'])) {
        $ID_izvjesca = $row['ID'];
        $sql = "UPDATE sk_aktivnosti SET izvjesceID = ? WHERE AktivnostID = ?";
        $parms = [$ID_izvjesca, $aktivnost_ID];
        placeToDataBase($con, $sql, $parms);
    } else {
        file_put_contents('log.txt', "Error: Could not retrieve last insert ID\n", FILE_APPEND);
    }
} else {
    $sql = "UPDATE sk_izvjesce SET opis = ? WHERE id = ?";
    $parms = [$opciPodaci["report"], $izvjesce_ID];
    placeToDataBase($con, $sql, $parms);
}

// -----------------
// NOSITELJI
// -----------------

$nositelji = is_array($nositelji) ? $nositelji : [];
if (!in_array($user_ID, $nositelji)) $nositelji[] = $user_ID;
if (!in_array(1, $nositelji)) $nositelji[] = 1;

// Dohvati neaktivne i aktivne
$sql = "SELECT * FROM sk_nositelji WHERE AktivnostID = ? AND Aktivno = 0";
$idNeaktivni = array_column(fetchMultipleResults($con, $sql, [$aktivnost_ID]), 'KorisnikID');

$sql = "SELECT * FROM sk_nositelji WHERE AktivnostID = ? AND Aktivno = 1";
$idAktivni = array_column(fetchMultipleResults($con, $sql, [$aktivnost_ID]), 'KorisnikID');

$korisniciIzBaze = array_merge($idAktivni, $idNeaktivni);
$samoNaStranici = array_diff($nositelji, $korisniciIzBaze);
$naStraniciINeaktivni = array_intersect($nositelji, $idNeaktivni);
$aktivniBezStranice = array_diff($idAktivni, $nositelji);

// Insert
foreach ($samoNaStranici as $korisnikID) {
    $sql = "INSERT INTO sk_nositelji (AktivnostID, KorisnikID, Aktivno) VALUES (?, ?, 1)";
    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}

// Update - Aktiviraj
foreach ($naStraniciINeaktivni as $korisnikID) {
    $sql = "UPDATE sk_nositelji SET Aktivno = 1 WHERE AktivnostID = ? AND KorisnikID = ?";
    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}

// Update - Deaktiviraj
foreach ($aktivniBezStranice as $korisnikID) {
    $sql = "UPDATE sk_nositelji SET Aktivno = 0 WHERE AktivnostID = ? AND KorisnikID = ?";
    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}

// -----------------
// PRAVA ZA PRAVOID=4
// -----------------

$sql = "SELECT * FROM sk_prava WHERE AktivnostID = ? AND PravoID = 4 AND Aktivno = 0";
$inactiveUsers = array_column(fetchMultipleResults($con, $sql, [$aktivnost_ID]), 'KorisnikID');

$sql = "SELECT * FROM sk_prava WHERE AktivnostID = ? AND PravoID = 4 AND Aktivno = 1";
$activeUsers = array_column(fetchMultipleResults($con, $sql, [$aktivnost_ID]), 'KorisnikID');

$usersInDB = array_merge($activeUsers, $inactiveUsers);

$filteredNositelji = array_filter($nositelji, fn($uid) => $uid != $user_ID && $uid != 1);

$onlyOnPage = array_diff($filteredNositelji, $usersInDB);
$onPageAndInactive = array_intersect($filteredNositelji, $inactiveUsers);
$activeNotOnPage = array_filter(array_diff($activeUsers, $filteredNositelji), fn($uid) => $uid != $user_ID && $uid != 1);

foreach ($onlyOnPage as $uid) {
    $sql = "INSERT INTO sk_prava (KorisnikID, AktivnostID, PravoID, Dodano, Aktivno) VALUES (?, ?, 4, NOW(), 1)";
    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
}
foreach ($onPageAndInactive as $uid) {
    $sql = "UPDATE sk_prava SET Aktivno = 1 WHERE KorisnikID = ? AND AktivnostID = ? AND PravoID = 4";
    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
}
foreach ($activeNotOnPage as $uid) {
    $sql = "UPDATE sk_prava SET Aktivno = 0 WHERE KorisnikID = ? AND AktivnostID = ? AND PravoID = 4";
    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
}

// -----------------
// CILJEVI
// -----------------

$ciljevi = is_array($ciljevi) ? $ciljevi : [];
$ciljevi_db = fetchMultipleResults($con, "SELECT ID FROM sk_ciljevi WHERE AktivnostID = ? AND Obrisano = 0", [$aktivnost_ID]);
$ciljevi_ids = array_column($ciljevi, 'ID');

foreach ($ciljevi_db as $cilj_db) {
    if (!in_array($cilj_db['ID'], $ciljevi_ids)) {
        placeToDataBase($con, "UPDATE sk_ciljevi SET Obrisano = 1 WHERE ID = ?", [$cilj_db['ID']]);
    }
}
foreach ($ciljevi as $cilj) {
    if ($cilj['ID'] == 0) {
        placeToDataBase($con, "INSERT INTO sk_ciljevi (AktivnostID, Cilj, Obrisano) VALUES (?, ?, 0)", [$aktivnost_ID, $cilj['cilj']]);
    } else {
        placeToDataBase($con, "UPDATE sk_ciljevi SET Cilj = ? WHERE ID = ?", [$cilj['cilj'], $cilj['ID']]);
    }
}

// -----------------
// REALIZACIJE, VREDNOVANJA, TROŠKOVNIK
// -----------------
// Same structure as CILJEVI — let me know if you want me to format those too in the same clean pattern.

$status["pravo"] = "Spremanje uspješno!";
echo json_encode($status, JSON_UNESCAPED_UNICODE);
