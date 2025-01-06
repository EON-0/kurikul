<?php

function popuni($id_aktivnosti){
    $serverName = "MASHINA\SQLEXPRESS";
    $connectionOptions = ["Database" => "skkurikul", "Uid" => "app", "PWD" => "pass"];
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) { die(print_r(sqlsrv_errors(), true)); }

    //za OPCENITO
    $sql_Aktivnost_Opcenit = "SELECT * FROM Aktivnost WHERE ID = ? AND Obrisano = 0";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Opcenit, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $Aktivnost_array = [
            "Naziv" => $row["Naziv"],
            "Vremenik" => $row["Vremenik"],
            "Kreirano" => ($row["Datum"]),
            "Namjena" => $row["Namjena"]
        ];

    //za CILJEVI
    $sql_Aktivnost_Ciljevi =  "SELECT Ciljevi.Cilj FROM Aktivnost JOIN Ciljevi ON Aktivnost.ID = Ciljevi.AktivnostID WHERE Aktivnost.ID = ? AND Aktivnost.Obrisano = 0";   
        $ciljevi;
        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Ciljevi, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $ciljevi[] = $row["Cilj"];
        }


    //za AUTORA
    $sql_Aktivnost_Autor = "SELECT Korisnici.FullName FROM Aktivnost 
        JOIN Aktivnosti ON Aktivnost.ID = Aktivnosti.AktivnostID
        JOIN Korisnici ON Aktivnosti.AutorID = Korisnici.ID
        WHERE Aktivnost.ID = ?;";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Autor, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   
        
        $Aktivnost_array["Autor"] = $row["FullName"];
    
    //za VRSTU AKTIVNOSTI
    $sql_Aktivnost_Vrsta_Aktivnosti = " SELECT VrsteAktivnosti.Naziv FROM Aktivnosti 
        JOIN VrsteAktivnosti ON Aktivnosti.VrstaID = VrsteAktivnosti.ID 
        WHERE Aktivnosti.AktivnostID = ? AND VrsteAktivnosti.Aktivno = 1;";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Vrsta_Aktivnosti, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   

        $Aktivnost_array["Vrsta_Aktivnosri"] = $row["Naziv"];

    //za STATUS
    $sql_Aktivnost_Status = "SELECT Statusi.Status FROM Aktivnosti 
        JOIN Statusi ON Aktivnosti.StatusID = Statusi.ID 
        WHERE Aktivnosti.AktivnostID = ?;";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Status, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   

        $Aktivnost_array["Status"] = $row["Status"];

    //za TROSKOVNIK
    $sql_Aktivnost_Troskovnik =  " SELECT Troskovnik.Trosak FROM Troskovnik  WHERE Troskovnik.AktivnostID = ? AND Troskovnik.Obrisano = 0;";   
        $troskovnik;
        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Troskovnik, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $troskovnik[] = $row["Trosak"];
        }

    //za REALIZACIJU
    $sql_Aktivnost_Realizacija =  "SELECT Realizacije.Realizacija FROM Realizacije WHERE Realizacije.AktivnostID = ? AND Obrisano = 0;";   
        $realizacija;
        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Realizacija, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $realizacija[] = $row["Realizacija"];
        }

    //za IZVJESCE
    $sql_Aktivnost_Izvjesce = "SELECT Izvjesce.opis FROM Aktivnosti
        JOIN Izvjesce on Aktivnosti.izvjesceID = Izvjesce.id 
        WHERE Aktivnosti.AktivnostID = ? AND Izvjesce.potvrdeno = 1;";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Izvjesce, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   

        $Aktivnost_array["Izvjesce"] = $row["opis"];


    //za KATEGORIJA NAPREDOVANJA
    $sql_Aktivnost_Kategoriju_Napredovanja = "SELECT kategorijaNapredovanje.grupa FROM Aktivnosti
        JOIN Izvjesce on Aktivnosti.izvjesceID = Izvjesce.id 
        JOIN kategorijaNapredovanje on kategorijaNapredovanje.id = Izvjesce.kategorijaNapredovanja
        WHERE Aktivnosti.AktivnostID = ? AND Izvjesce.potvrdeno = 1;";

        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Kategoriju_Napredovanja, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   

        $Aktivnost_array["Kategoriju_Napredovanja"] = $row["grupa"];


    //za NACIN VREDNOVANJA
    $sql_Aktivnost_Nacin_Vrednovanja =  "SELECT Vrednovanja.Vrednovanje FROM Vrednovanja WHERE AktivnostID = ? AND Obrisano = 0;";   
        $nacin_vrednovanja;
        $stmt = sqlsrv_query($conn, $sql_Aktivnost_Nacin_Vrednovanja, [$id_aktivnosti]);
        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));}
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            $nacin_vrednovanja[] = $row["Vrednovanje"];
        }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    /*
    //ispis
    print($Aktivnost_array["Naziv"] . "<br>");
    print($Aktivnost_array["Vremenik"]. "<br>");
    echo $Aktivnost_array["Kreirano"]->format('d.m.Y H:i:s') . "<br>";
    print($Aktivnost_array["Namjena"]. "<br>");

    //ispis
    foreach ($ciljevi as $cilj) {
        echo $cilj . "<br>";
    }
    //ispis
    print($Aktivnost_array["Autor"] . "<br>");
    //ispis
    print($Aktivnost_array["Vrsta_Aktivnosri"] . "<br>");
    //ispis
    print($Aktivnost_array["Status"] . "<br>");

    //ispis
    foreach ($troskovnik as $trosak) {
        echo $trosak . "<br>";
    }
    //ispis
    foreach ($realizacija as $real) {
        echo $real . "<br>";
    }
    //ispis
    print($Aktivnost_array["Izvjesce"] . "<br>");
    //ispis
    print($Aktivnost_array["Kategoriju_Napredovanja"] . "<br>");    

    //ispis
    foreach ($nacin_vrednovanja as $nacin) {
        echo $nacin . "<br>";
    }
    */
    
}   
?>
