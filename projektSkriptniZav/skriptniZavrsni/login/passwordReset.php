<?php
include '../dbasse_conn.php';
session_start();

// Remove or update this header if you intend to output HTML
header('Content-Type: text/html');

if (!isset($_GET['token'])) {
    echo "Token is missing.";
    exit;
}

$token = $_GET['token'];


$sql = "UPDATE sk_resetLozinke SET kliknuto = 1 WHERE token = ? LIMIT 1";
$params = [$token];
$updateResult = placeToDataBase($con, $sql, $params);

// Fetch the token row
$sql = "SELECT * FROM sk_resetLozinke WHERE token = ? LIMIT 1";
$params = [$token];
$row = fetchSingleResult($con, $sql, $params);

if (!$row) {
    echo "Token nije pronađen.";
    exit;
}

$tokenTime = strtotime($row['vrijeme']);
$currentTime = time();
$diffSeconds = $currentTime - $tokenTime;

if ($diffSeconds < 30 * 60) {
    echo file_get_contents('form.html');
    echo "<script> let userID = " . json_encode($row['korisnikID']) . "; let token = " . json_encode($token) . ";</script>";
} else {
    echo "<p>Vrijeme za promjenu lozinke je isteklo</p>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>

<body>
</body>

</html>