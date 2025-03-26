<?php

session_start();

include '../dbasse_conn.php';



$user_ID = $_SESSION['user_ID']; //autorova id

$aktivnost_ID = $_POST['aktivnost_ID'];



//if isset = true then $_POST[] else null

$opciPodaci = isset($_POST['opciPodaci']) ? $_POST['opciPodaci'] : null;

$nositelji = isset($_POST['nositelji']) ? $_POST['nositelji'] : null;

$ciljevi = isset($_POST['ciljevi']) ? $_POST['ciljevi'] : null;

$troskovnik = isset($_POST['troskovnik']) ? $_POST['troskovnik'] : null;

$vrednovanja = isset($_POST['vrednovanje']) ? $_POST['vrednovanje'] : null;

$realizacije = isset($_POST['realizacije']) ? $_POST['realizacije'] : null;



$sql = "SELECT 

            CASE 

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

    // Provjera prava

    $sql = "SELECT 

                CASE 

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



// Provjera da li korisnik ima pravo uređivanja

if (!$pravo || $pravo['imaPravo'] != 1) {

    echo json_encode(["pravo" => "Nemate pravo uređivanja!"]);

    exit;
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



if ($izvjesce_ID === -1 || $izvjesce_ID === NULL) {

    // Insert new record into sk_izvjesce

    $sql = "INSERT INTO sk_izvjesce (opis, potvrdeno, potvrdenoAdminID, generiranaPotvrda, urBroj, kategorijaNapredovanja)

    VALUES (?, 0, -1, 0, -1, 1)";

    $parms = [$opciPodaci["report"]];

    placeToDataBase($con, $sql, $parms);



    // Get last inserted ID

    $sql = "SELECT ID FROM sk_izvjesce ORDER BY ID DESC LIMIT 1;";

    $row = fetchSingleResult($con, $sql, []);



    if ($row && isset($row['ID'])) {

        $ID_izvjesca = $row['ID'];



        // Update sk_aktivnosti with the new izvjesceID

        $sql = "UPDATE sk_aktivnosti SET izvjesceID = ? WHERE AktivnostID = ?;";

        $parms = [$ID_izvjesca, $aktivnost_ID];

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



//nositelji i prava



if (!is_array($nositelji)) {

    $nositelji = [];
}



// Ensure that the author is always included, so they cannot be removed.

if (!in_array($user_ID, $nositelji)) {

    $nositelji[] = $user_ID; // current user must always have rights

}

if (!in_array(1, $nositelji)) { // assuming admin id is 1

    $nositelji[] = 1; // admin must always have rights

}



// 1. Fetch inactive users from the database for this activity.

$sql = "SELECT * FROM `sk_nositelji` WHERE AktivnostID = ? AND Aktivno = 0;";

$nositeljiNeaktivni = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

// Extract KorisnikID from each row.

$idNeaktivni = array_map(function ($redak) {

    return $redak['KorisnikID'];
}, $nositeljiNeaktivni);



// 2. Fetch active users from the database for this activity.

$sql = "SELECT * FROM `sk_nositelji` WHERE AktivnostID = ? AND Aktivno = 1;";

$nositeljiAktivni = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

$idAktivni = array_map(function ($redak) {

    return $redak['KorisnikID'];
}, $nositeljiAktivni);



// Combine all users (active and inactive) from the database.

$korisniciIzBaze = array_merge($idAktivni, $idNeaktivni);



// Determine operations using the POST data from the page (using $nositelji)

// 1) Users only on the page (not in the database) -> INSERT

$samoNaStranici = array_diff($nositelji, $korisniciIzBaze);



// 2) Users sent from the page that are currently inactive in the database -> UPDATE (activate)

$naStraniciINeaktivni = array_intersect($nositelji, $idNeaktivni);



// 3) Users active in the database but not sent from the page -> UPDATE (deactivate)

$aktivniBezStranice = array_diff($idAktivni, $nositelji);



// INSERT users that are only on the page 

foreach ($samoNaStranici as $korisnikID) {

    $sql = "INSERT INTO sk_nositelji (AktivnostID, KorisnikID, Aktivno) VALUES (?, ?, 1);";

    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}



// UPDATE: Activate users that are sent from the page but currently inactive in the database.

foreach ($naStraniciINeaktivni as $korisnikID) {

    $sql = "UPDATE sk_nositelji SET Aktivno = 1 WHERE AktivnostID = ? AND KorisnikID = ?;";

    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}



// UPDATE: Deactivate users that are active in the database but not sent from the page.

foreach ($aktivniBezStranice as $korisnikID) {

    $sql = "UPDATE sk_nositelji SET Aktivno = 0 WHERE AktivnostID = ? AND KorisnikID = ?;";

    placeToDataBase($con, $sql, [$aktivnost_ID, $korisnikID]);
}



// Example variables:

// $con         -> your database connection

// $user_ID     -> current user id (from session or input)

// $nositelji  -> array of user IDs coming from the form (for rights assignment)



// Ensure the current user and admin always have rights.



// -----------------------------------------

// 1. Fetch rights entries for PravoID = 4 from table sk_prava

// -----------------------------------------



// 1. Fetch rows only for this AktivnostID and PravoID=4



$sql = "SELECT * 

        FROM sk_prava

        WHERE AktivnostID = ?

          AND PravoID = 4

          AND Aktivno = 0;";

$inactiveRights = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

$inactiveUsers = array_map(function ($row) {

    return $row['KorisnikID'];
}, $inactiveRights);



$sql = "SELECT *

        FROM sk_prava

        WHERE AktivnostID = ?

          AND PravoID = 4

          AND Aktivno = 1;";

$activeRights = fetchMultipleResults($con, $sql, [$aktivnost_ID]);

$activeUsers = array_map(function ($row) {

    return $row['KorisnikID'];
}, $activeRights);



// Combine users (active or inactive) for this activity & pravo=4

$usersInDB = array_merge($activeUsers, $inactiveUsers);



// 2. If you *do NOT* want to force the author/admin into PravoID=4,

//    remove or skip them from the $nositelji array before the diff logic:

$filteredNositelji = array_filter($nositelji, function ($uid) use ($user_ID) {

    // skip the author and admin

    return ($uid != $user_ID && $uid != 1);
});



// 3. Determine which operations to perform:

$onlyOnPage       = array_diff($filteredNositelji, $usersInDB);   // Insert

$onPageAndInactive = array_intersect($filteredNositelji, $inactiveUsers); // Activate

$activeNotOnPage  = array_diff($activeUsers, $filteredNositelji);       // Deactivate



// Optionally skip deactivating the author/admin:

$activeNotOnPage = array_filter($activeNotOnPage, function ($uid) use ($user_ID) {

    return ($uid != $user_ID && $uid != 1);
});



// 4. Execute database operations, *including AktivnostID*:

foreach ($onlyOnPage as $uid) {

    $sql = "INSERT INTO sk_prava (KorisnikID, AktivnostID, PravoID, Dodano, Aktivno)

            VALUES (?, ?, 4, NOW(), 1)";

    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
}



foreach ($onPageAndInactive as $uid) {

    $sql = "UPDATE sk_prava

            SET Aktivno = 1

            WHERE KorisnikID = ?

              AND AktivnostID = ?

              AND PravoID = 4;";

    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
}



foreach ($activeNotOnPage as $uid) {

    $sql = "UPDATE sk_prava

            SET Aktivno = 0

            WHERE KorisnikID = ?

              AND AktivnostID = ?

              AND PravoID = 4;";

    placeToDataBase($con, $sql, [$uid, $aktivnost_ID]);
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
