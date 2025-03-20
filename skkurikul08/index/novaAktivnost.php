<?php
session_start();
include '../dbasse_conn.php';

$user_ID = 1024;/*$_SESSION['user_ID'];*/


//if isset = true then $_POST[] else null
$opciPodaci = isset($_POST['opciPodaci']) ? $_POST['opciPodaci'] : null;
$nositelji = isset($_POST['nositelji']) ? $_POST['nositelji'] : null;
$ciljevi = isset($_POST['ciljevi']) ? $_POST['ciljevi'] : null;
$troskovnik = isset($_POST['troskovnik']) ? $_POST['troskovnik'] : null;
$vrednovanja = isset($_POST['vrednovanje']) ? $_POST['vrednovanje'] : null;
$realizacije = isset($_POST['realizacije']) ? $_POST['realizacije'] : null;

$data = [
    'user_ID' => $user_ID ?? 'null',
    'aktivnost_ID' => $aktivnost_ID ?? 'null',
    'opciPodaci' => $opciPodaci ?? 'null',
    'nositelji' => $nositelji ?? 'null',
    'ciljevi' => $ciljevi ?? 'null',
    'troskovnik' => $troskovnik ?? 'null',
    'vrednovanje' => $vrednovanje ?? 'null',
    'realizacije' => $realizacije ?? 'null'
];  

//za debug i saki slucaj
$content = print_r($data, true);
file_put_contents('log.txt', $content, FILE_APPEND);

//sk_aktivnost 

$sql = "INSERT INTO sk_aktivnost (Naziv, Namjena, Vremenik, Datum, Obrisano) VALUES (?, ?, ?, ?, 0);";
$parms = [$opciPodaci["name"], $opciPodaci["purpose"], $opciPodaci["timeline"], $opciPodaci["created"]];
placeToDataBase($con, $sql, $parms);


//ID kreirane aktivnosti
$sql = "SELECT ID FROM sk_aktivnost ORDER BY ID DESC LIMIT 1";
$row = fetchSingleResult($con,$sql,[]); //1468
$aktivnost_ID = $row["ID"];

//izvjesce (prvo to ka onda morem id odma deti u aktivnosti, makar bu opis prazni)
$sql = "INSERT INTO sk_izvjesce (opis, potvrdeno, potvrdenoAdminID, generiranaPotvrda, urBroj, kategorijaNapredovanja)
VALUES (?, 0, -1, 0, -1, 1);";
placeToDataBase($con, $sql, [$opciPodaci["report"]]);

$sql = "SELECT ID FROM sk_izvjesce ORDER BY ID DESC LIMIT 1;";
$row = fetchSingleResult($con,$sql,[]);
$izvjesce_ID = $row["ID"];
//aktivnosti 

$sql = "INSERT INTO sk_aktivnosti (AutorID, AktivnostID, VrstaID, StatusID, izvjesceID, skolskaGodina)
VALUES (?, ?, ?, ?, ?, ?);";
$trenutnaGodina = date("Y");
$parms = [$user_ID,$aktivnost_ID,$opciPodaci["activity-type"],$opciPodaci["status"],$izvjesce_ID,$trenutnaGodina];

placeToDataBase($con, $sql,$parms);


//ciljevi

if (!is_null($ciljevi)) { 
foreach ($ciljevi as $cilj) {
        $sql = "INSERT INTO sk_ciljevi (AktivnostID, Cilj, Obrisano) VALUES (?, ?, 0);";
        $parms = [$aktivnost_ID, $cilj['cilj']];
        placeToDataBase($con, $sql, $parms);
    }
}

//realivacija

if (!is_null($realizacije)) { 
    foreach ($realizacije as $realizacija) {
            $sql = "INSERT INTO sk_realizacije (AktivnostID, Realizacija, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $realizacija['realizacija']];
            placeToDataBase($con, $sql, $parms);
    }
}


//vrednovanje

if (!is_null($vrednovanja)) { 
    foreach ($vrednovanja as $vrednovanje) {
            $sql = "INSERT INTO sk_vrednovanja (AktivnostID, Vrednovanje, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $vrednovanje['vrednovanje']];
            placeToDataBase($con, $sql, $parms);
    }
}

//troskovnik

if (!empty($troskovnik)) { 
    foreach ($troskovnik as $trosak) {
            $sql = "INSERT INTO sk_troskovnik (AktivnostID, Trosak, Obrisano) VALUES (?, ?, 0);";
            $parms = [$aktivnost_ID, $trosak['trosak']];
            placeToDataBase($con, $sql, $parms);
    }
}

//nositelj i prava jos
$status["pravo"] = "Nova aktivnost uspjesno spremljena";
echo json_encode($status, JSON_UNESCAPED_UNICODE);

?>
