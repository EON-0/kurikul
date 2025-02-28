<?php
include '../dbasse_local.php';

$dropdowns_array = [];

// Fetch vrsta aktivnosti
$sql = "SELECT * FROM sk_vrsteaktivnosti WHERE Aktivno = 1";
$vrstaAktivnosti = fetchMultipleResults($con, $sql, []);
$dropdowns_array[0] = $vrstaAktivnosti;

// Fetch statusi
$sql = "SELECT * FROM sk_statusi";
$statusi = fetchMultipleResults($con, $sql, []);
$dropdowns_array[1] = $statusi;  // Corrected this line

//echo json_encode($dropdowns_arra,);

echo json_encode($dropdowns_array, JSON_UNESCAPED_UNICODE); //unicode za hrvatska slova
?>