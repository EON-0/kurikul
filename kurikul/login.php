
<?php
session_start();
include_once 'password_hash.php';
//mozda je ono prije ne delalo jer si ne posval login_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    //provjeravam ako je post postavljen, ako je to ne dobro nist se ne izvodi
    $username_form = $_POST['username'] ?? null;    //provjerava ako postoji username v postu, ako ne postavi v null
    $password_form = $_POST['password'] ?? null;    //--||--

    if (!$username_form || !$password_form) {   //password i usernam ne uneseni trazi unos
        die("Molimo unesite korisničko ime i lozinku.");
    }
$username_form = $_POST['username'];
$password_form = $_POST['password'];

$serverName = "DESKTOP-3GFGVTG\SQLEXPRESS";
$connectionOptions = [
    "Database" => "kurikulum",
    "Uid" => "aplikacija",
    "PWD" => "pass"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {die(print_r(sqlsrv_errors(), true));}

$sql = "SELECT * FROM Korisnici WHERE Username = ?";
$params = [$username_form];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {die(print_r(sqlsrv_errors(), true));} //ako stmt nema nista, onda se aplikacija ugasi

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);   //spremi celu tablicu v row, prstupam row['ime_stupca'];
if ($row === null) {$messege = "Korisničko ime ne postoji!";}
else{
    if ($username_form === $row['Username'] && checkPassword($password_form,$row['Password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
    header("Location: test.html");
    exit();}   
    else{$messege = "Kriva lozinka!";}

    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login</title>
</head>
<body>
    <div class="login-container">
    <img src=".\logo\tsck.png" alt="Logo" class="logo">
        <form method="POST" action="">

        <?php if (isset($messege)): //provjeri ako messeg ima kaj ?> 
            <p class="error"><?php echo $messege; ?></p>
        <?php endif; //ako je ne ovo gori se ne dela ?>

            <label for="username">Korisničko ime:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Lozinka:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>
</html>
