<?php
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

// Extract the number
$id_aktivnosti = $data['number'] ?? 0;

$serverName = "MASHINA\SQLEXPRESS";
$connectionOptions = [
    "Database" => "skkurikul",
    "Uid" => "app",
    "PWD" => "pass",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(json_encode(["error" => sqlsrv_errors()]));
}

$Aktivnost_array = [];

// Helper function to fetch a single result
function fetchSingleResult($conn, $sql, $params) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(json_encode(["error" => sqlsrv_errors()]));
    }
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

// Helper function to fetch multiple results
function fetchMultipleResults($conn, $sql, $params) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(json_encode(["error" => sqlsrv_errors()]));
    }
    $results = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    return $results;
}

// OPCENITO
$sql = "SELECT Naziv, Vremenik, Datum AS Kreirano, Namjena FROM Aktivnost WHERE ID = ? AND Obrisano = 0";
$row = fetchSingleResult($conn, $sql, [$id_aktivnosti]);
if ($row) {
    $Aktivnost_array = array_merge($Aktivnost_array, $row);
}

// CILJEVI
$sql = "SELECT Cilj FROM Ciljevi WHERE AktivnostID = ? AND Obrisano = 0";
//$ciljevi = fetchMultipleResults($conn, $sql, [$id_aktivnosti]);
//$Aktivnost_array['Ciljevi'] = array_column($ciljevi, 'Cilj');

// AUTOR
$sql = "SELECT Korisnici.FullName AS Autor FROM Aktivnosti 
        JOIN Korisnici ON Aktivnosti.AutorID = Korisnici.ID 
        WHERE Aktivnosti.AktivnostID = ?";
$row = fetchSingleResult($conn, $sql, [$id_aktivnosti]);
if ($row) {
    $Aktivnost_array['Autor'] = $row['Autor'];
}

// VRSTA AKTIVNOSTI
$sql = "SELECT VrsteAktivnosti.ID AS Vrsta_Aktivnosti FROM Aktivnosti 
        JOIN VrsteAktivnosti ON Aktivnosti.VrstaID = VrsteAktivnosti.ID 
        WHERE Aktivnosti.AktivnostID = ? AND VrsteAktivnosti.Aktivno = 1";
$row = fetchSingleResult($conn, $sql, [$id_aktivnosti]);
if ($row) {
    $Aktivnost_array['Vrsta_Aktivnosti'] = $row['Vrsta_Aktivnosti'];
}

// STATUS
$sql = "SELECT Statusi.ID AS Status FROM Aktivnosti 
        JOIN Statusi ON Aktivnosti.StatusID = Statusi.ID 
        WHERE Aktivnosti.AktivnostID = ?";
$row = fetchSingleResult($conn, $sql, [$id_aktivnosti]);
if ($row) {
    $Aktivnost_array['Status'] = $row['Status'];
}

// TROSKOVNIK
$sql = "SELECT Trosak FROM Troskovnik WHERE AktivnostID = ? AND Obrisano = 0";
//$troskovnik = fetchMultipleResults($conn, $sql, [$id_aktivnosti]);
//$Aktivnost_array['Troskovnik'] = array_column($troskovnik, 'Trosak');

// REALIZACIJA
$sql = "SELECT Realizacija FROM Realizacije WHERE AktivnostID = ? AND Obrisano = 0";
//$realizacija = fetchMultipleResults($conn, $sql, [$id_aktivnosti]);
//$Aktivnost_array['Realizacija'] = array_column($realizacija, 'Realizacija');

// IZVJESCE
$sql = "SELECT opis AS Izvjesce FROM Izvjesce 
        JOIN Aktivnosti ON Aktivnosti.izvjesceID = Izvjesce.id 
        WHERE Aktivnosti.AktivnostID = ? AND Izvjesce.potvrdeno = 1";
$row = fetchSingleResult($conn, $sql, [$id_aktivnosti]);
if ($row) {
    $Aktivnost_array['Izvjesce'] = $row['Izvjesce'];
}

// NACIN VREDNOVANJA
$sql = "SELECT Vrednovanje FROM Vrednovanja WHERE AktivnostID = ? AND Obrisano = 0";
//vrednovanja = fetchMultipleResults($conn, $sql, [$id_aktivnosti]);
//$Aktivnost_array['Nacin_Vrednovanja'] = array_column($vrednovanja, 'Vrednovanje');

// Debug before sending
if (json_encode($Aktivnost_array) === false) {
    echo json_encode(["error" => "JSON encoding failed: " . json_last_error_msg()]);
    exit;
}

// Send the response
echo json_encode($Aktivnost_array, JSON_UNESCAPED_UNICODE);
