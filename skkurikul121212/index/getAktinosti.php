<?php
include '../dbasse_conn.php';
// Daje sve aktivnosti za pojedinog korisnika

$user_ID = $_POST["user_ID"];
$vrstaAktivnosti_ID = $_POST["odabir"];
$aktualnaGodina = $_POST["aktualnaGodina"];


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
$godina = date("Y");
if ($aktualnaGodina == 1) {
        $sql .= " AND sk_aktivnosti.skolskaGodina = ?";
        $parms[] = $godina;
}

$aktivnosti_array = fetchMultipleResults($con, $sql, $parms);
echo json_encode($aktivnosti_array, JSON_UNESCAPED_UNICODE);
