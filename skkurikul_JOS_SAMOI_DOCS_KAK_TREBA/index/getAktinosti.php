<?php
include '../dbasse_conn.php';

// Retrieve and sanitize POST parameters
$user_ID = isset($_POST['user_ID']) ? $_POST['user_ID'] : 0;
$vrstaAktivnosti_ID = isset($_POST['odabir']) ? $_POST['odabir'] : -1;
$aktualnaGodina = isset($_POST['aktualnaGodina']) ? $_POST['aktualnaGodina'] : 0;

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
        $sql = "SELECT DISTINCT sk_aktivnost.* 
            FROM sk_aktivnost 
            JOIN sk_nositelji ON sk_aktivnost.ID = sk_nositelji.AktivnostID
            JOIN sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
            WHERE sk_nositelji.KorisnikID = ? 
            AND sk_aktivnost.Obrisano = 0";
        $parms = [$user_ID];
}

// Determine the relevant year based on the current month
if (date("n") < 9) {
        $godina = date("Y") - 1; // Use previous year if before September
} else {
        $godina = date("Y");     // Use current year if September or later
}

// If 'aktualnaGodina' is set, filter by the defined date range
if ($aktualnaGodina == 1) {
        $start_date = $godina . "-09-01";
        $end_date = ($godina + 1) . "-07-01";
        $sql .= " AND sk_aktivnost.Datum BETWEEN ? AND ?";
        $parms[] = $start_date;
        $parms[] = $end_date;
}

// If a specific activity type is selected, add it to the query
if ($vrstaAktivnosti_ID != -1) {
        $sql .= " AND sk_aktivnosti.VrstaID = ?";
        $parms[] = $vrstaAktivnosti_ID;
}

// Append the ORDER BY clause
$sql .= " ORDER BY sk_aktivnost.Naziv ASC";

// Execute the query and return the JSON result
$aktivnosti_array = fetchMultipleResults($con, $sql, $parms);
echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
