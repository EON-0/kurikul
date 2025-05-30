<?php
include '../session_timeout.php';
include '../dbasse_conn.php';

checkSessionTime(); //provjera trajanja sessije

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

$user_ID = 1024; //$_SESSION['user_ID']; //za test after del    
$fullName = $_SESSION['FullName'];
print("<script>const user_ID = {$user_ID}; const fullName = '" . addslashes($fullName) . "';</script>");


?>
<!DOCTYPE html>
<html lang="hr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Školski Kurikulum</title>
    <!--<link rel="stylesheet" href="index.css">-->
    <link rel="stylesheet" href="index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="index.js"></script>
    <script src="events.js"></script>

</head>

<body>
    <div class="form-container">
        <div class="left-panel">
            <div class="field-group">
                <img src="../login/logo/tsck.png" alt="Logo" class="logo"><br>
                <span id="welcome-message"><?php echo $_SESSION['FullName'] ?></span>
                <button id="logout" onclick="logOut()" class="odjava">ODJAVA</button>

                <label for="vrste-aktivnosti">Vrste Aktivnosti:</label>
                <select id="vrste-aktivnosti"></select>
            </div>
            <div class="field-group">
                <label>
                    <span>Sve/Aktualna godina</span>
                    <input type="checkbox" id="aktualna-godina">
                </label>
                <div class="scrollable-div" id="scrollable-div">

                </div>
            </div>
            <div class="field-group">
                <button id="nova-aktivnost" onclick="kreiraAktivnost()">Nova Aktivnost</button>
                <button id="kopiraj">Kopiraj</button>
            </div>
        </div>

        <div class="right-panel">
            <form id="aktivnost-form">
                <div class="container">
                    <div class="full-width">
                        <label for="name">Naziv:</label>
                        <input type="text" id="name" name="naziv">
                    </div>
                    <div>
                        <label for="created">Kreirano:</label>
                        <input type="date" id="created" name="created">
                    </div>
                    <div>
                        <label for="author">Autor:</label>
                        <input type="text" id="author" name="author">
                    </div>
                    <div>
                        <label for="activity-type">Vrsta Aktivnosti:</label>
                        <select id="activity-type" name="activity-type"></select>
                    </div>
                    <div>
                        <label for="status">Status:</label>
                        <select id="status" name="status"></select>
                    </div>
                    <div>
                        <label for="list-goals">Ciljevi:</label>
                        <div class="list-box" id="list-goals"></div>
                        <div class="button-group">
                            <button type="button" id="add-goal" >+</button>
                            <button type="button" id="remove-goal" >-</button>
                            <button type="button" id="edit-goal" >Uredi</button>
                        </div>
                    </div>
                    <div>
                        <label for="purpose">Namjena:</label>
                        <textarea id="purpose" name="purpose" rows="4"></textarea>
                    </div>
                    <div>
                        <label for="list-realizations">Način realizacije:</label>
                        <div class="list-box" id="list-realizations"></div>
                        <div class="button-group">
                            <button type="button" id="add-realization">+</button>
                            <button type="button" id="remove-realization">-</button>
                            <button type="button" id="edit-realization">Uredi</button>
                        </div>
                    </div>
                    <br>
                    <div>
                        <label for="carriers">Nositelji:</label>
                        <div class="list-box" id="carriers"></div>
                        <input type="text" id="search-carriers" placeholder="Pretraži">
                    </div>
                    <div>
                        <label for="timeline">Vremenik:</label>
                        <textarea id="timeline" name="timeline" rows="4"></textarea>
                    </div>
                    <div class="full-width">
                        <label for="list-evaluation">Način vrednovanja i način korištenja rezultata:</label>
                        <div class="list-box" id="list-evaluation"></div>
                        <div class="button-group">
                            <button type="button" id="add-evaluation">+</button>
                            <button type="button" id="remove-evaluation">-</button>
                            <button type="button" id="edit-evaluation">Uredi</button>
                        </div>
                    </div>
                    <div>
                        <label for="expenses">Troškovnik:</label>
                        <div class="list-box" id="expenses">
                        </div>
                        <div class="button-group">
                            <button type="button" id="add-expense">+</button>
                            <button type="button" id="remove-expense">-</button>
                            <button type="button" id="edit-expense">Uredi</button>
                        </div>
                    </div>
                    <div class="full-width">
                        <label for="report">Izvješće realizacije:</label>
                        <textarea id="report" name="report" rows="6"></textarea>
                    </div>
                    <div class="buttons">
                    <button type="button" id="delete-button" class="button_obrisi">Obriši</button>
                    <button type="button" id="cancel-button">Odustani</button>
                    <button type="button" id="save-button">Spremi</button>
                    <button type="button" id="confirm-button">Potvrdi</button>
                    <button type="reset" id="reset-button">Odbaci</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</body>