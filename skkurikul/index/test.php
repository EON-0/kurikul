<?php
include '../dbasse_conn.php';


$user_ID = 1024;
$aktivnost_ID = 1168;

$opciPodaci = [
    "author" => "Krešimir Kočiš",
    "carriers" => "Razvoj softvera - grupni rad na projektu",
    "created" => "2023-09-17",
    "evaluation" => "",
    "expenses" => "Razvoj softvera - grupni rad na projektu",
    "name" => "Razvoj softvera - grupni rad na projektu",
    "purpose" => "Zainteresiranim učenicima naše škole.",
    "report" => "",
    "responsibility" => "Neznam kaj je to",
    "status" => "3",
    "timeline" => "Kroz školsku godinu"
];
update($con,$opciPodaci,$aktivnost_ID);

function update($con,$opciPodaci,$aktivnost_ID) {
    $sql = "UPDATE sk_aktivnost
    SET Naziv = ?, 
        Namjena = ?,
        Vremenik = ?, 
        Datum = STR_TO_DATE(?, '%Y-%m-%d')
    WHERE ID = ?"; 
$parms = [$opciPodaci["name"], $opciPodaci["purpose"], $opciPodaci["timeline"], $opciPodaci["created"], $aktivnost_ID];
placeToDataBase($con, $sql, $parms);

}
?>
