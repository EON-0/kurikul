<?php
session_start();
if (isset($_SESSION['loggedin']) && ($_SESSION['loggedin'] === true))  {
    $usern = $_SESSION['username'];
    echo "Hello, $usern <br/>";
} else {
    header("Location: login.php");
    exit();
}
    
$serverName = "DESKTOP-3GFGVTG\SQLEXPRESS";
$connectionOptions = [
    "Database" => "kurikulum",
    "Uid" => "aplikacija",
    "PWD" => "pass"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {die(print_r(sqlsrv_errors(), true));}

$sql = "SELECT * FROM Aktivnost WHERE ID=1023";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   //spremi celu tablicu v row, prstupam row['ime_stupca'];
print($row["Naziv"]);


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>testing</title>
</head>
<body>

</body>
</html>