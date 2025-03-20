<?php
include '../dbasse_conn.php';                   


$user_ID = $_GET['user_ID'];
$aktivnost_ID = $_GET['aktivnost_ID'];

//if isset = true then $_POST[] else null
$opciPodaci = isset($_GET['opciPodaci']) ? $_GET['opciPodaci'] : null;
$nositelji = isset($_GET['nositelji']) ? $_GET['nositelji'] : null;
$ciljevi = isset($_GET['ciljevi']) ? $_GET['ciljevi'] : null;
$troskovnik = isset($_GET['troskovnik']) ? $_GET['troskovnik'] : null;
$vrednovanje = isset($_GET['vrednovanje']) ? $_GET['vrednovanje'] : null;
$realizacije = isset($_GET['realizacije']) ? $_GET['realizacije'] : null;

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
    $id = update($con,$opciPodaci,$aktivnost_ID);
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
    $izvjesce_ID = $row['izvjesceID'];

    return $izvjesce_ID;

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
