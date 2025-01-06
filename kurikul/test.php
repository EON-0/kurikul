<?php
$serverName = "MASHINA\SQLEXPRESS";
$connectionOptions = ["Database" => "skkurikul", "Uid" => "app", "PWD" => "pass"];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) { die(print_r(sqlsrv_errors(), true)); }
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Školski Kurikulum</title>
    <link rel="stylesheet" href="style.css">
    <script src="index.js"></script>

</head>
<script>
    $(document).ready(function () {
        $(".myButton").click(function () {
            let buttonValue = $(this).val();
            $.ajax({
                url: "process.php", // PHP script
                type: "POST",
                data: { value: buttonValue },
                success: function (response) {
                    let data = JSON.parse(response); // Parse JSON response
                    $("#result").html(data.message); // Update HTML
                },
                error: function () {
                    alert("An error occurred.");
                },
            });
        });
    });
</script>
<body>
<?php
                    $user_ID = 1042;
                        $sql = "SELECT DISTINCT Aktivnost.Naziv,Aktivnost.ID FROM Aktivnost JOIN Prava ON Aktivnost.ID = Prava.AktivnostID WHERE Prava.KorisnikID = ?";
                        $stmt = sqlsrv_query($conn, $sql,[$user_ID]);
                        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

                    
                          while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ // Spremi cijelu tablicu u row, pristupam row['ime_stupca'];
                            print("<button class='myButton' name='ID_aktivnosti' value='{$row["ID"]}'>{$row['Naziv']}</button>");
                        }
?>
                    <div id='result'></div>

</body>
</html>