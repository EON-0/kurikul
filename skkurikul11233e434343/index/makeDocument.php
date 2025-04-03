<?php
// Retrieve POST data
$opciPodaci   = $_POST['opciPodaci'] ?? [];
$ciljevi      = $_POST['ciljevi'] ?? [];
$nositelji    = $_POST['nositelji'] ?? [];
$realizacije  = $_POST['realizacije'] ?? [];
$vrednovanja  = $_POST['vrednovanja'] ?? [];
$troskovnik   = $_POST['troskovnik'] ?? [];

/*
Assumption: The $opciPodaci array contains multiple pieces of information for each row.
For instance, it is expected to be structured like:
    $opciPodaci = [
       'aktivnost' => [...],
       'namjena'   => [...],
       'vremenik'  => [...],
    ];
If your data structure is different, adjust the code accordingly.
*/

// Determine the number of rows (assuming all arrays are of equal length)
$rowCount = count($opciPodaci['aktivnost'] ?? []);

// Prepare filename for download
$filename = "document.html";

// Set headers so that the browser downloads the file
header('Content-Type: text/html; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Begin HTML output
echo '<html>';
echo '<head><meta charset="UTF-8"><title>Dokument</title></head>';
echo '<body>';
echo '<table border="1" cellspacing="0" cellpadding="5">';

// Table header
echo '<tr>';
echo '<th>Aktivnost</th>';
echo '<th>Ciljevi aktivnosti</th>';
echo '<th>Namjena aktivnosti</th>';
echo '<th>Nositelji aktivnosti i njihova odgovornost</th>';
echo '<th>Naèin realizacije aktivnosti</th>';
echo '<th>Vremenik aktivnosti</th>';
echo '<th>Naèin vrednovanja i naèin korištenja rezultata</th>';
echo '<th>Troškovnik</th>';
echo '</tr>';

// Loop through rows and output each cell value, using htmlspecialchars() for safety
for ($i = 0; $i < $rowCount; $i++) {
    $aktivnost          = $opciPodaci['aktivnost'][$i] ?? '';
    $namjena            = $opciPodaci['namjena'][$i] ?? '';
    $vremenik           = $opciPodaci['vremenik'][$i] ?? '';
    $ciljeviAktivnosti  = $ciljevi[$i] ?? '';
    $nositeljiAktivnosti = $nositelji[$i] ?? '';
    $nacinRealizacije   = $realizacije[$i] ?? '';
    $vrednovanjaRez    = $vrednovanja[$i] ?? '';
    $troskovnikAkt     = $troskovnik[$i] ?? '';

    echo '<tr>';
    echo '<td>' . htmlspecialchars($aktivnost) . '</td>';
    echo '<td>' . htmlspecialchars($ciljeviAktivnosti) . '</td>';
    echo '<td>' . htmlspecialchars($namjena) . '</td>';
    echo '<td>' . htmlspecialchars($nositeljiAktivnosti) . '</td>';
    echo '<td>' . htmlspecialchars($nacinRealizacije) . '</td>';
    echo '<td>' . htmlspecialchars($vremenik) . '</td>';
    echo '<td>' . htmlspecialchars($vrednovanjaRez) . '</td>';
    echo '<td>' . htmlspecialchars($troskovnikAkt) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';

exit;
