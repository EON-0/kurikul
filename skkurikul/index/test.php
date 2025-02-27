<?php
include '../dbasse_local.php';
$sql = "SELECT * from sk_pravo WHERE id = ?";
$id = 1; // Ensure $id is an array
$rez = fetchSingleResult($con, $sql, [$id]);

if ($rez) { 
    /*
    foreach ($rez as $key => $value) {
        echo "$key: $value<br>";
    }
    */
    echo $rez['ID'];
    echo "<br>";
    echo $rez['Naziv'];
    echo "<br>";
    echo $rez['Opis'];

} else {
    echo "No result found.";
}
?>
