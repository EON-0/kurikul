<?php
session_start();
include '../dbasse_conn.php';

$user_ID = $_SESSION['user_ID']; //autorova id
$aktivnost_ID = $_POST['aktivnost_ID'];

if ($user_ID == 1) {  // Admin ima sva prava
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
                          AND PravoID IN (1,2)
                    ) THEN 1
                    ELSE 0
                END AS imaPravo"; //samo autor i admin  more obrisati 
    $pravo = fetchSingleResult($con, $sql, [$user_ID, $aktivnost_ID]);
}

// Provjera da li korisnik ima pravo uređivanja
if (!$pravo || $pravo['imaPravo'] != 1) {
    echo json_encode(["pravo" => "Nemate pravo uređivanja!"]);
    exit;
}
$sql = "UPDATE sk_aktivnost SET Obrisano = 1 WHERE ID = ?;";
$parms = [$aktivnost_ID];
placeToDataBase($con, $sql, $parms);

$status["status"] = "Uspješno obrisano!";

echo json_encode($status, JSON_UNESCAPED_UNICODE);
