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
            WHERE sk_aktivnost.Obrisano = 0";
        $parms = [];
} else {
        if ($vrstaAktivnosti_ID != -1) {
                $sql = "SELECT DISTINCT sk_aktivnost.* 
                FROM sk_aktivnost 
                JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
                JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
                WHERE sk_nositelji.KorisnikID = ? 
                  AND sk_aktivnosti.VrstaID = ? 
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
// Determine the correct year based on the current month (using numeric month)
if (date("n") < 9) {
        // If the current month is < 9 (September), use last year
        $godina = date("Y") - 1;
} else {
        // Otherwise, use the current year
        $godina = date("Y");
}

// If filtering by the current year is enabled, include both $godina and $godina + 1
if ($aktualnaGodina == 1) {
        // Use IN (?, ?) to allow either the base year or the next year
        $sql .= " AND YEAR(sk_aktivnost.Datum) IN (?)";
        $parms[] = $godina;
}

$sql .= " ORDER BY sk_aktivnost.Naziv ASC";
// Fetch the results and return them as JSON
$aktivnosti_array = fetchMultipleResults($con, $sql, $parms);
echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
