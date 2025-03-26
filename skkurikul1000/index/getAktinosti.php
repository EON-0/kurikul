<?php
include '../dbasse_conn.php';

// Retrieve and sanitize POST parameters
$user_ID = isset($_POST["user_ID"]) ? $_POST["user_ID"] : 0;
$vrstaAktivnosti_ID = isset($_POST["odabir"]) ? $_POST["odabir"] : -1;
$aktualnaGodina = isset($_POST["aktualnaGodina"]) ? $_POST["aktualnaGodina"] : 0;

// Check if the user has administrative rights
$sql = "SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM sk_prava
                    WHERE AktivnostID IS NULL
                      AND PravoID IN (5)
                      AND Aktivno = 1
                      AND KorisnikID = ?
                ) THEN 1
                ELSE 0
            END AS imaPravo";

$administratorskaOvlast = fetchSingleResult($con, $sql, [$user_ID]);

// Build the main query based on administrative rights and selected activity type
if ($administratorskaOvlast && $administratorskaOvlast['imaPravo'] == 1) {
        $sql = "SELECT DISTINCT sk_aktivnost.* 
            FROM sk_aktivnost 
            JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
            JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID 
            WHERE sk_aktivnost.Obrisano = 0
            ";
        $parms = [];
} else {
        if ($vrstaAktivnosti_ID != -1) {
                $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
                WHERE sk_nositelji.KorisnikID = ? 
                  AND sk_aktivnost.Obrisano = 0";
                $parms = [$user_ID, $vrstaAktivnosti_ID];
        } else {
                $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
                WHERE sk_nositelji.KorisnikID = ? 
                  AND sk_aktivnost.Obrisano = 0";
                $parms = [$user_ID];
        }
}



if (date("n") < 9) {
        $godina = date("Y") - 1; //od godine prije do ovo
} else {
        $godina = date("Y"); //samo ovu godinu i sljedeću
}
if ($aktualnaGodina == 1) {

        $start_date = $godina . "-09-01";
        $end_date = ($godina + 1) . "-07-01";
        $sql .= " AND sk_aktivnost.Datum BETWEEN ? AND ?";
        $parms[] = $start_date;
        $parms[] = $end_date;
}

if ($vrstaAktivnosti_ID != -1) {
        $sql .= " AND sk_aktivnosti.VrstaAktivnostiID = ?";
        $parms = [$user_ID, $vrstaAktivnosti_ID];
}



// tu treba deti ka bode vrsta aktivnost s sql.=
$sql .= " ORDER BY sk_aktivnost.Naziv ASC";
$aktivnosti_array = fetchMultipleResults($con, $sql, $parms);
echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
