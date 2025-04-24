<?php
session_start();
include '../dbasse_conn.php';

if (isset($_POST['year'])) {
    $_SESSION['year'] = $_POST['year'];
}

//određivanje godinje
$base_year = isset($_SESSION['year']) ? $_SESSION['year'] : -1;

$start_date = date($base_year . '-09-01');
$end_date = date(($base_year + 1) . '-07-01');


//dobivanje ID-eva vrsta aktivnosti
$sql = "SELECT ID,Naziv FROM sk_vrsteaktivnosti WHERE Aktivno = 1;";
$row = fetchMultipleResults($con, $sql, []);
$ID_vrste = array_column($row, 'ID');
$Naziv_vrste = array_column($row, 'Naziv');

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
    sk_aktivnost.Obrisano = 0 AND sk_aktivnosti.VrstaID = ? AND Datum BETWEEN ? AND ? 
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
    sk_aktivnost.Obrisano = 0 AND sk_aktivnosti.VrstaID = ?
    ORDER BY 
    sk_aktivnosti.VrstaID,
    sk_aktivnost.Naziv ASC;";

    $parms = [];
}

$aktivnosti_po_vrsti = [];

// Prolazak kroz sve vrste aktivnosti
foreach ($ID_vrste as $vrsta_id) {
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
            sk_aktivnost.Obrisano = 0 AND sk_aktivnosti.VrstaID = ? AND Datum BETWEEN ? AND ? 
        ORDER BY 
            sk_aktivnosti.VrstaID,
            sk_aktivnost.Naziv ASC;";

        $parms = [$vrsta_id, $start_date, $end_date];
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
            sk_aktivnost.Obrisano = 0 AND sk_aktivnosti.VrstaID = ?
        ORDER BY 
            sk_aktivnosti.VrstaID,
            sk_aktivnost.Naziv ASC;";

        $parms = [$vrsta_id];
    }

    // Dohvati aktivnosti za trenutnu vrstu
    $rezultat = fetchMultipleResults($con, $sql_aktivnost, $parms);

    // Stavi ih u array pod indeksom vrste
    $aktivnosti_po_vrsti[$vrsta_id] = $rezultat;
}
echo "<style>
        table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
        font-family: Arial, sans-serif;
    }

    th {
        background-color: #90c14f;
        color: black;
        font-style: italic;
        padding: 10px;
        text-align: left;
    }

    td {
        border: 1px solid #ccc;
        padding: 10px;
        vertical-align: top;
    }

    td:first-child {
        width: 250px;
        font-weight: bold;
        background-color: #f2f2f2;
    }

    h3 {
        margin-top: 40px;
        font-family: Arial, sans-serif;
    }
    .aktivnost-tabela {
        border-collapse: collapse;
        width: 80%;
        margin-bottom: 30px;
        font-family: Arial, sans-serif;
    }
    .aktivnost-tabela th {
        background-color: #90c14f;
        color: black;
        text-align: left;
        padding: 10px;
        font-style: italic;
    }
    .aktivnost-tabela td {
        border: 1px solid #ccc;
        padding: 10px;
        vertical-align: top;
    }
    .aktivnost-tabela td:first-child {
        width: 250px;
        font-weight: bold;
        background-color: #f2f2f2;
    }
</style>";
$vrste_nazivi_map = array_combine($ID_vrste, $Naziv_vrste);

foreach ($aktivnosti_po_vrsti as $vrsta_id => $aktivnosti) {
    $vrsta_naziv = $vrste_nazivi_map[$vrsta_id] ?? "Nepoznata vrsta ($vrsta_id)";
    echo "<h3>" . htmlspecialchars($vrsta_naziv) . "</h3>";

    echo "<table>";
    echo "<tr>
            <th>RB</th>
            <th>Područje aktivnosti</th>
            <th>Nositelj - nastavnik</th>
            <th>Namjena</th>
            <th>Vremenik</th>
          </tr>";

    $rb = 1;
    foreach ($aktivnosti as $aktivnost) {
        $aktivnost_ID = $aktivnost['ID'];

        $naziv = $aktivnost['Naziv'] ?? '';
        $namjena = fetchSingleResult($con, "SELECT Namjena FROM sk_aktivnost WHERE ID = ?", [$aktivnost_ID])['Namjena'] ?? '';
        $vremenik = fetchSingleResult($con, "SELECT Vremenik FROM sk_aktivnost WHERE ID = ?", [$aktivnost_ID])['Vremenik'] ?? '';

        $nositelji = fetchMultipleResults($con, "SELECT sk_korisnici.FullName AS Nositelj 
                        FROM sk_korisnici 
                        JOIN sk_nositelji ON sk_nositelji.KorisnikID = sk_korisnici.ID 
                        WHERE sk_nositelji.AktivnostID = ? AND sk_nositelji.Aktivno = 1", [$aktivnost_ID]);
        $nositelj_str = implode(", ", array_column($nositelji, 'Nositelj'));

        echo "<tr>
                <td>" . $rb++ . "</td>
                <td>" . htmlspecialchars($naziv) . "</td>
                <td>" . htmlspecialchars($nositelj_str) . "</td>
                <td>" . htmlspecialchars($namjena) . "</td>
                <td>" . htmlspecialchars($vremenik) . "</td>
              </tr>";
    }

    echo "</table>";
}

foreach ($aktivnosti_po_vrsti as $vrsta_id => $aktivnosti) {
    // Dohvati naziv vrste
    $index = array_search($vrsta_id, $ID_vrste);
    $naziv_vrste = $Naziv_vrste[$index] ?? "Nepoznata vrsta";

    echo "<h2>$naziv_vrste</h2>";

    foreach ($aktivnosti as $aktivnost) {
        $aktivnost_ID = $aktivnost['ID'];
        $data = dohvatiPodatkeZAktivnost($con, $aktivnost_ID); // Tvoja funkcija za dohvat

        $opcenito = $data[0];
        $ciljevi = implode("<br>", array_column($data[1], 'Cilj'));
        $troskovi = implode("<br>", array_column($data[2], 'Trosak'));
        $realizacija = implode("<br>", array_column($data[3], 'Realizacija'));
        $vrednovanja = implode("<br>", array_column($data[4], 'Vrednovanje'));
        $nositelji_imena = dohvatiImenaNositelja($con, $data[5]);
        $nositelji = implode("<br>", $nositelji_imena);

        echo "
            <table class='aktivnost-tabela'>
                <thead>
                    <tr>
                        <th>Aktivnost</th>
                        <th>{$aktivnost['Naziv']}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Ciljevi aktivnosti</td><td>$ciljevi</td></tr>
                    <tr><td>Namjena aktivnosti</td><td>{$opcenito['Namjena']}</td></tr>
                    <tr><td>Nositelji aktivnosti i njihova odgovornost</td><td>$nositelji</td></tr>
                    <tr><td>Način realizacije aktivnosti</td><td>$realizacija</td></tr>
                    <tr><td>Vremenik aktivnosti</td><td>{$opcenito['Vremenik']}</td></tr>
                    <tr><td>Način vrednovanja i način korištenja rezultata</td><td>$vrednovanja</td></tr>
                    <tr><td>Troškovnik</td><td>$troskovi</td></tr>
                </tbody>
            </table>";
    }
}

function dohvatiPodatkeZAktivnost($con, $aktivnost_ID)
{
    // ovo je tvoja već postojeća logika, premještena u funkciju
    $opci_array = [];

    $sql_querry[] = "SELECT Naziv, Vremenik, Datum AS Kreirano, Namjena FROM sk_aktivnost WHERE ID = ? AND Obrisano = 0;";
    $sql_querry[] = "SELECT sk_korisnici.FullName AS Autor FROM sk_aktivnosti JOIN sk_korisnici ON sk_aktivnosti.AutorID = sk_korisnici.ID WHERE sk_aktivnosti.AktivnostID = ?";
    $sql_querry[] = "SELECT sk_vrsteaktivnosti.ID AS Vrsta_Aktivnosti FROM sk_aktivnosti JOIN sk_vrsteaktivnosti ON sk_aktivnosti.VrstaID = sk_vrsteaktivnosti.ID WHERE sk_aktivnosti.AktivnostID = ? AND sk_vrsteaktivnosti.Aktivno = 1";
    $sql_querry[] = "SELECT sk_statusi.ID AS Status FROM sk_aktivnosti JOIN sk_statusi ON sk_aktivnosti.StatusID = sk_statusi.ID WHERE sk_aktivnosti.AktivnostID = ?";
    $sql_querry[] = "SELECT opis AS Izvjesce FROM sk_izvjesce JOIN sk_aktivnosti ON sk_aktivnosti.izvjesceID = sk_izvjesce.id WHERE sk_aktivnosti.AktivnostID = ?";
    $sql_querry[] = "SELECT potvrdeno FROM sk_izvjesce JOIN sk_aktivnosti ON sk_aktivnosti.izvjesceID = sk_izvjesce.id WHERE sk_aktivnosti.AktivnostID = ?";

    foreach ($sql_querry as $sql) {
        $row = fetchSingleResult($con, $sql, [$aktivnost_ID]);
        if ($row) {
            $opci_array = array_merge($opci_array, $row);
        }
    }

    $return_array = array_fill(0, 6, []);
    $return_array[0] = $opci_array;

    $sql_querry_multiple[] = "SELECT sk_ciljevi.ID, Cilj FROM sk_ciljevi WHERE AktivnostID = ? AND Obrisano = 0";
    $sql_querry_multiple[] = "SELECT sk_troskovnik.ID, Trosak FROM sk_troskovnik WHERE AktivnostID = ? AND Obrisano = 0";
    $sql_querry_multiple[] = "SELECT sk_realizacije.ID, Realizacija FROM sk_realizacije WHERE AktivnostID = ? AND Obrisano = 0";
    $sql_querry_multiple[] = "SELECT sk_vrednovanja.ID, Vrednovanje FROM sk_vrednovanja WHERE AktivnostID = ? AND Obrisano = 0";
    $sql_querry_multiple[] = "SELECT sk_korisnici.ID FROM sk_korisnici JOIN sk_nositelji ON sk_nositelji.KorisnikID = sk_korisnici.ID WHERE sk_nositelji.AktivnostID = ? AND sk_nositelji.Aktivno = 1";

    foreach ($sql_querry_multiple as $index => $sql) {
        $row = fetchMultipleResults($con, $sql, [$aktivnost_ID]);
        $return_array[$index + 1] = $row ?: [];
    }

    return $return_array;
}

function dohvatiImenaNositelja($con, $ids_array)
{
    if (empty($ids_array)) return [];

    $ids = implode(",", array_column($ids_array, 'ID'));
    $sql = "SELECT FullName FROM sk_korisnici WHERE ID IN ($ids)";
    $results = fetchMultipleResults($con, $sql, []);

    return array_column($results, 'FullName');
}
