<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Školski Kurikulum</title>
    <link rel="stylesheet" href="style.css">
    <script src="index.js"></script>

</head>
<body>
    <?php
    session_start();
    $id_aktivnosti = 1051;

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) {
        header("Location: login.php");
        exit();
    }

    $serverName = "MASHINA\SQLEXPRESS";
    $connectionOptions = ["Database" => "skkurikul", "Uid" => "app", "PWD" => "pass"];
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) { die(print_r(sqlsrv_errors(), true)); }

    $user_ID = $_SESSION['ID']; 

        //ID -> FullName
        $sql = "SELECT * FROM Korisnici WHERE Korisnici.ID = ?";
        $stmt = sqlsrv_query($conn, $sql, [$user_ID]);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $full_name = $row['FullName'];
    

    echo $id_aktivnosti
    ?>

    <div class="form-container">
        <div class="left-panel">
            <div class="field-group">
                <img src=".\logo\tsck.png" alt="Logo" class="logo"><br>
                <?php echo "Pozdrav, $full_name"; ?>
                <button type="submit" name="logout" class ="odjava">ODJAVA</button>

                <label for="vrste-aktivnosti">Vrste Aktivnosti:</label>
                <select id="vrste-aktivnosti">
                    <option>autorske</option>
                </select>


            </div>
            <div class="field-group">
                <label><input type="checkbox" id="aktualna-godina"> Sve / Aktualna godina</label><br>
                
                <div class="scrollable-div">

                    <?php
                    $user_ID = 1042;//DELETAJ 
                        $sql = "SELECT DISTINCT Aktivnost.Naziv,Aktivnost.ID FROM Aktivnost JOIN Prava ON Aktivnost.ID = Prava.AktivnostID WHERE Prava.KorisnikID = ?";
                        $stmt = sqlsrv_query($conn, $sql,[$user_ID]);
                        if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

                    
                          while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ // Spremi cijelu tablicu u row, pristupam row['ime_stupca'];
                            print("<button type='submit' name='ID_aktivnosti' value='{$row["ID"]}'>{$row['Naziv']}</button>");
                        }
                        
                    ?>
                </div>   
            </div>
            <div class="field-group">
                <button>Nova Aktivnost</button>
                <button>Kopiraj</button>
            </div>
        </div>

        <div class="right-panel">
                <form>
                <div class="container">
                <div class="full-width">

                    <?php  $sql = "SELECT Aktivnost.Naziv FROM Aktivnost WHERE Aktivnost.ID = ?";
                    $stmt = sqlsrv_query($conn, $sql,[$id_aktivnosti]);
                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    ?>
                        <label for="name">Naziv:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['Naziv'], ENT_QUOTES); ?>">
                    </div>          

                    <div>
                        <label for="created">Kreirano:</label>
                        <input type="text" id="created" name="created">
                    </div>
                    <div>
                        <label for="author">Autor:</label>
                        <input type="text" id="author" name="author">
                    </div>
                    <div>
                        <label for="activity-type">Vrsta Aktivnosti:</label>
                        <select id="activity-type" name="activity-type">
                        <?php
                         $sql = "SELECT * FROM VrsteAktivnosti WHERE Aktivno = 1;";
                         $stmt = sqlsrv_query($conn, $sql);
                         if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

                         while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ // Spremi cijelu tablicu u row, pristupam row['ime_stupca'];
                             print("<option value='{$row['ID']}'>{$row['Naziv']}</option>");}
                        ?>
                        </select>
                    </div>
                    <div>
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <?php
                                $sql = "SELECT * FROM Statusi";
                                $stmt = sqlsrv_query($conn, $sql);
                                if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

                                while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ // Spremi cijelu tablicu u row, pristupam row['ime_stupca'];
                                    print("<option value='{$row['ID']}'>{$row['Status']}</option>");
                                }
                            ?>
                        </select>
                    </div>

                    

                    <div>
                        <label for="list-goals">Ciljevi:</label>
                        <div class="list-box" id="list-goals"></div>
                        <div class="button-group">
                            <button type="button">+</button>
                            <button type="button">-</button>
                            <button type="button">Uredi</button>
                        </div>
                    </div>
                    <div>
                        <label for="purpose">Namjena:</label>
                        <textarea id="purpose" name="purpose" rows="4"></textarea>
                    </div>
                    <div>
                        <label for="realization-method">Način realizacije:</label>
                        <textarea id="realization-method" name="realization-method" rows="4"></textarea>
                    </div>
                    <div>
                        <label for="list-realizations">Realizacije:</label>
                        <div class="list-box" id="list-realizations"></div>
                        <div class="button-group">
                            <button type="button">+</button>
                            <button type="button">-</button>
                            <button type="button">Uredi</button>
                        </div>
                    </div>

                    <div>
                        <label for="carriers">Nositelji:</label>
                        <div class="list-box" id="carriers"></div>
                        <input type="text" placeholder="Pretraži">
                    </div>
                    <div>
                        <label for="responsibility">Odgovornost:</label>
                        <textarea id="responsibility" name="responsibility" rows="4"></textarea>
                    </div>
                    <div>
                        <label for="timeline">Vremenik:</label>
                        <textarea id="timeline" name="timeline" rows="4"></textarea>
                    </div>

                    <div class="full-width">
                        <label for="evaluation">Način vrednovanja i način korištenja rezultata:</label>
                        <div class="list-box" id="evaluation"></div>
                        <div class="button-group">
                            <button type="button">+</button>
                            <button type="button">-</button>
                            <button type="button">Uredi</button>
                        </div>
                    </div>

                    <div>
                        <label for="expenses">Troškovnik:</label>
                        <div class="list-box" id="expenses"></div>
                        <div class="button-group">
                            <button type="button">+</button>
                            <button type="button">-</button>
                            <button type="button">Uredi</button>
                        </div>
                    </div>
                    <div>
                        <label for="progress-category">Kategorija napredovanja:</label>
                        <select id="progress-category" name="progress-category">
                            <option value="">Odaberite</option>
                        </select>
                    </div>

                    <div class="full-width">
                        <label for="report">Izvješće realizacije:</label>
                        <textarea id="report" name="report" rows="6"></textarea>
                    </div>

                    <div class="buttons">
                        <button type="button" class = "button_obrisi">Obriši</button>
                        <button type="button">Odustani</button>
                        <button type="submit">Spremi</button>
                        <button type="button">Potvrdi</button>
                        <button type="reset">Odbaci</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
