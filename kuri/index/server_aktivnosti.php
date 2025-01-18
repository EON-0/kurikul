<?php
    // Check if 'User_ID' is passed in the AJAX request
    if (isset($_GET['User_ID'])) {
        $user_ID = intval($_GET['User_ID']); // Get the User_ID from the GET request
    } else {
        die(json_encode(['error' => 'User_ID is missing']));
    }

    $serverName = "MASHINA\SQLEXPRESS";
    $connectionOptions = [
        "Database" => "skkurikul",
        "Uid" => "app",
        "PWD" => "pass",
        "CharacterSet" => "UTF-8"
    ];

    // Connect to SQL Server
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) {
        die(json_encode(["error" => sqlsrv_errors()]));
    }

    // SQL query
    $sql = "SELECT DISTINCT Aktivnost.Naziv, Aktivnost.ID 
        FROM Aktivnost 
        JOIN Prava ON Aktivnost.ID = Prava.AktivnostID 
        WHERE Prava.KorisnikID = ? AND Aktivno = 1;";
    $stmt = sqlsrv_query($conn, $sql, [$user_ID]);

    if ($stmt === false) {
        die(json_encode(['error' => sqlsrv_errors()], JSON_PRETTY_PRINT)); 
    }

    // Fetch results into an array
    $activities = []; 
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $activities[$row['ID']] = $row['Naziv']; 
    }
    // Return results as JSON
    echo json_encode($activities, JSON_PRETTY_PRINT); 
?>