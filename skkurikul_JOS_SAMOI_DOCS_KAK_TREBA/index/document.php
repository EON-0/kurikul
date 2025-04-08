<?php
session_start();
include '../dbasse_conn.php';

if (isset($_POST['year'])) {
    $_SESSION['year'] = $_POST['year'];
}

$base_year = isset($_SESSION['year']) ? $_SESSION['year'] : -1;


$start_date = date($base_year . '-09-01');
$end_date = date(($base_year + 1) . '-07-01');

if ($base_year != -1) {
    $sql_aktivnost = "SELECT 
    sk_aktivnost.ID,
    sk_aktivnost.Naziv,
    sk_aktivnosti.VrstaID
FROM 
    sk_aktivnost
JOIN 
    sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
WHERE 
    sk_aktivnost.Obrisano = 0 AND Datum BETWEEN ? AND ?
ORDER BY 
    sk_aktivnosti.VrstaID,
    sk_aktivnost.Naziv ASC;";
    $parms = [$start_date, $end_date];
} else {
    $sql_aktivnost = "SELECT 
    sk_aktivnost.ID,
    sk_aktivnost.Naziv,
    sk_aktivnosti.VrstaID
FROM 
    sk_aktivnost
JOIN 
    sk_aktivnosti ON sk_aktivnost.ID = sk_aktivnosti.AktivnostID
WHERE 
    sk_aktivnost.Obrisano = 0
ORDER BY 
    sk_aktivnosti.VrstaID,
    sk_aktivnost.Naziv ASC;";
    $parms = [];
}


$aktivnost_results = fetchMultipleResults($con, $sql_aktivnost, $parms);

// 3. Find intersection of both sets (IDs that appear in both)
$aktivnost_IDs = array_column($aktivnost_results, 'ID');

echo "<style>
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }
    th, td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .activity-header {
        background-color: #e6f7ff !important;
        font-size: 1.1em;
    }
    .separator {
        background-color: #f8f9fa;
        height: 5px;
    }
</style>";

echo "<table>";

// Process each activity ID
foreach ($aktivnost_IDs as $aktivnost_ID) {
    $opci_array = [];
    $sql_querry = [];

    // General data queries (optimized)
    $sql_querry = [
        "SELECT Naziv, Vremenik, Datum AS Kreirano, Namjena FROM sk_aktivnost WHERE ID = ? AND Obrisano = 0",
        "SELECT sk_korisnici.FullName AS Autor FROM sk_aktivnosti JOIN sk_korisnici ON sk_aktivnosti.AutorID = sk_korisnici.ID WHERE sk_aktivnosti.AktivnostID = ?",
        "SELECT sk_vrsteaktivnosti.Naziv AS Vrsta_Aktivnosti FROM sk_aktivnosti JOIN sk_vrsteaktivnosti ON sk_aktivnosti.VrstaID = sk_vrsteaktivnosti.ID WHERE sk_aktivnosti.AktivnostID = ? AND sk_vrsteaktivnosti.Aktivno = 1",
        "SELECT sk_statusi.Status FROM sk_aktivnosti JOIN sk_statusi ON sk_aktivnosti.StatusID = sk_statusi.ID WHERE sk_aktivnosti.AktivnostID = ?",
        "SELECT opis AS Izvjesce FROM sk_izvjesce JOIN sk_aktivnosti ON sk_aktivnosti.izvjesceID = sk_izvjesce.id WHERE sk_aktivnosti.AktivnostID = ?",
        "SELECT IF(potvrdeno = 1, 'Da', 'Ne') AS Potvrdeno FROM sk_izvjesce JOIN sk_aktivnosti ON sk_aktivnosti.izvjesceID = sk_izvjesce.id WHERE sk_aktivnosti.AktivnostID = ?"
    ];

    // Fetch general data
    foreach ($sql_querry as $sql) {
        $row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
        if ($row) {
            $opci_array = array_merge($opci_array, $row);
        }
    }

    $return_array = array_fill(0, 6, []);
    $return_array[0] = $opci_array;

    // Multiple-value queries (improved with FullName for nositelji)
    $sql_querry_multiple = [
        "SELECT Cilj FROM sk_ciljevi WHERE AktivnostID = ? AND Obrisano = 0",
        "SELECT Trosak FROM sk_troskovnik WHERE AktivnostID = ? AND Obrisano = 0",
        "SELECT Realizacija FROM sk_realizacije WHERE AktivnostID = ? AND Obrisano = 0",
        "SELECT Vrednovanje FROM sk_vrednovanja WHERE AktivnostID = ? AND Obrisano = 0",
        "SELECT sk_korisnici.FullName AS Nositelj FROM sk_korisnici JOIN sk_nositelji ON sk_nositelji.KorisnikID = sk_korisnici.ID WHERE sk_nositelji.AktivnostID = ? AND sk_nositelji.Aktivno = 1"
    ];

    // Fetch multiple-value data
    foreach ($sql_querry_multiple as $index => $sql) {
        $rows = fetchMultipleResults($con, $sql, [$aktivnost_ID]);
        $return_array[$index + 1] = $rows ? array_column($rows, isset($rows[0]['Nositelj']) ? 'Nositelj' : key($rows[0])) : [];
    }

    // Extract general data and keys
    $opciData = $return_array[0];
    $opciKeys = array_keys($opciData);

    // Headers for multiple-value columns
    $multipleHeaders = ["Ciljevi", "Troškovnik", "Realizacija", "Način vrednovanja", "Nositelji"];

    // Determine max rows for this activity
    $maxRows = max(array_map('count', array_slice($return_array, 1))) ?: 1;

    // Add a header row for the activity ID
    echo "<tr class='activity-header'><th colspan='" . (count($opciKeys) + count($multipleHeaders)) . "'>Aktivnost ID: $aktivnost_ID - " . htmlspecialchars($opciData['Naziv'] ?? '') . "</th></tr>";

    // Build the header row (column names)
    echo "<tr>";
    foreach ($opciKeys as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    foreach ($multipleHeaders as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }
    echo "</tr>";

    // Output the first row (with general data and first multiple-value entries)
    echo "<tr>";
    foreach ($opciData as $value) {
        echo "<td rowspan='$maxRows'>" . htmlspecialchars($value) . "</td>";
    }

    // Output first row of multiple-value data
    for ($i = 1; $i < count($return_array); $i++) {
        echo "<td>" . htmlspecialchars($return_array[$i][0] ?? '') . "</td>";
    }
    echo "</tr>";

    // Output remaining rows (only multiple-value data)
    for ($rowIndex = 1; $rowIndex < $maxRows; $rowIndex++) {
        echo "<tr>";
        for ($i = 1; $i < count($return_array); $i++) {
            echo "<td>" . htmlspecialchars($return_array[$i][$rowIndex] ?? '') . "</td>";
        }
        echo "</tr>";
    }

    // Add a separator row between activities
    echo "<tr class='separator'><td colspan='" . (count($opciKeys) + count($multipleHeaders)) . "'></td></tr>";
}

echo "</table>";
