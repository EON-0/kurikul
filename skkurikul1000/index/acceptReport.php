<?php
session_start();
include '../dbasse_conn.php';

$user_ID = $_SESSION['user_ID']; //autorova id
$aktivnost_ID = $_POST['aktivnost_ID'];


$pravo = ['imaPravo' => 0];
$sql = "SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM sk_prava
                    WHERE AktivnostID IS NULL
                      AND PravoID IN (6, 7, 8, 9)
                      AND Aktivno = 1
                      AND KorisnikID = ?
                ) THEN 1
                ELSE 0
            END AS imaPravo";
$administratorskaOvlast = fetchSingleResult($con, $sql, [$user_ID]);

if ($administratorskaOvlast && $administratorskaOvlast['imaPravo'] == 1) {
    $pravo = ['imaPravo' => 1];
}
if (!$pravo || $pravo['imaPravo'] != 1) {
    echo json_encode(["pravo" => "Nemate pravo uređivanja!"]);
    exit;
}

$sql = "SELECT izvjesceID FROM sk_aktivnosti WHERE AktivnostID = ?";
$row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
$izvjesce_ID = $row['izvjesceID'];

$sql = "UPDATE sk_izvjesce SET potvrdeno = 1, potvrdenoAdminID = ? WHERE ID = ?;";
$parms = [$user_ID, $izvjesce_ID];
placeToDataBase($con, $sql, $parms);

$status["status"] = "Uspješno potvrđeno!";

echo json_encode($status, JSON_UNESCAPED_UNICODE);
