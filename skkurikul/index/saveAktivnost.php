<?php
include '../dbasse_conn.php';                   


$user_ID = $_POST['user_ID'];
$aktivnost_ID = $_POST['aktivnost_ID'];

//if isset = true then $_POST[] else null
$opciPodaci = isset($_POST['opciPodaci']) ? $_POST['opciPodaci'] : null;
$nositelji = isset($_POST['nositelji']) ? $_POST['nositelji'] : null;
$ciljevi = isset($_POST['ciljevi']) ? $_POST['ciljevi'] : null;
$troskovnik = isset($_POST['troskovnik']) ? $_POST['troskovnik'] : null;
$vrednovanje = isset($_POST['vrednovanje']) ? $_POST['vrednovanje'] : null;
$realizacije = isset($_POST['realizacije']) ? $_POST['realizacije'] : null;

// SQL query to check permissions
$sql = "SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM sk_prava
            WHERE KorisnikID = ? 
            AND AktivnostID = ? 
            AND PravoID IN (1, 2)
        ) THEN 1
        ELSE 0
    END AS imaPravo";


$pravo = fetchSingleResult($con, $sql, [$user_ID, $aktivnost_ID]);

if($pravo['imaPravo'] == 0){
    $status["pravo"] = "Nemate pravo uređivanja!";
}
else{
    update($con,$opciPodaci,$aktivnost_ID);
    $status["pravo"] = "Uspjesno spremljeno!";
    //kod za spremanje
}
echo json_encode($status, JSON_UNESCAPED_UNICODE);

//sprema prva ime,namjenu,vremenik,kreirano
function update($con,$opciPodaci,$aktivnost_ID) {
        $sql = "UPDATE sk_aktivnost 
    SET Naziv = (SELECT ?), 
        Namjena = (SELECT ?), 
        Vremenik = (SELECT ?), 
        Datum = (SELECT STR_TO_DATE(?, '%Y-%m-%d')) 
    WHERE ID = ?";

    $parms = [$opciPodaci["name"], $opciPodaci["purpose"], $opciPodaci["timeline"], $opciPodaci["created"], $aktivnost_ID];
    placeToDataBase($con, $sql, $parms);

        //vrsta aktivnosti
        $sql = "UPDATE sk_aktivnosti 
        SET VrstaID = (SELECT ID FROM sk_vrsteaktivnosti WHERE ID = ? AND Aktivno = 1) 
        WHERE AktivnostID = ?";
    
    $parms = [$opciPodaci["activity-type"], $aktivnost_ID];
    placeToDataBase($con, $sql, $parms);


    $sql = "UPDATE sk_aktivnosti 
    SET StatusID = (SELECT ID FROM sk_statusi WHERE ID = ?) 
    WHERE AktivnostID = ?;";
    
    $parms = [$opciPodaci["status"], $aktivnost_ID];
    placeToDataBase($con, $sql, $parms);



    // Get the report ID
    $sql = "SELECT izvjesceID FROM sk_aktivnosti WHERE AktivnostID = ?";
    $row = fetchSingleResult($con, $sql, [$aktivnost_ID]);

    $izvjesce_ID = $row ? $row['izvjesceID'] : null;

    if (is_null($izvjesce_ID) || $izvjesce_ID == -1) { // Report doesn't exist
        if (!empty($opciPodaci["report"])) { // Something is written in the report field
            $sql = "INSERT INTO sk_izvjesce (opis, potvrdeno, potvrdenoAdminID, generiranaPotvrda, urBroj, kategorijaNapredovanja)
                    VALUES (?, 0, -1, 0, -1, 1)";
            placeToDataBase($con, $sql, [$opciPodaci["report"]]);

            // Get the last inserted ID
            $idSql = "SELECT LAST_INSERT_ID() AS new_id";
            $newRow = fetchSingleResult($con, $idSql, []);
            $new_izvjesce_ID = $newRow ? $newRow['new_id'] : null;

            if ($new_izvjesce_ID) {
                // Update sk_aktivnosti with the new izvjesceID
                $updateSql = "UPDATE sk_aktivnosti SET izvjesceID = ? WHERE AktivnostID = ?";
                placeToDataBase($con, $updateSql, [$new_izvjesce_ID, $aktivnost_ID]);
            } else {
                echo "Greška: Nije moguće dobiti ID novog izvješća.";
            }
        }
    } else { // Report exists, update it
        $sql = "UPDATE sk_izvjesce SET opis = ? WHERE id = ?";
        placeToDataBase($con, $sql, [$opciPodaci["report"], $izvjesce_ID]);
    }

//primjer podataka koje dobijem jsonom

/*
activity-type:"1"
author:"Krešimir Kočiš"  
carriers:"Razvoj softvera - grupni rad na projektu"
created:"2023-09-17"
evaluation:""
expenses:"Razvoj softvera - grupni rad na projektu"
name:"Razvoj softvera - grupni rad na projektu"
purpose:"Zainteresiranim učenicima naše škole."
report:""
responsibility:"Neznam kaj je to"
status:"3"
timeline:"Kroz školsku godinu"

*/
?>
