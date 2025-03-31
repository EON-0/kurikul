<?php
include '../dbasse_conn.php';
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check for required input
if (!isset($_POST['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nedostaju podaci.']);
    exit;
}

$email = $_POST['username'];

// Get user ID from sk_korisnici table
$sql = "SELECT ID FROM sk_korisnici WHERE Username = ? LIMIT 1";
$params = [$email];
$row = fetchSingleResult($con, $sql, $params);
if ($row) {
    $userID = $row['ID'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Korisničko ime ne postoji']);
    exit;
}

// Generate a unique token
$token = generateToken();
$resetURL = "http://tsck.eu/skolskiKurikul/login/passwordReset.php?token={$token}";
// Insert token into sk_token table
$sql = "INSERT INTO sk_token (korisnikID, token, kliknuto, promijenjeno) VALUES (?, ?, 0, 0)";
$params = [$userID, $token];
$insertResult = placeToDataBase($con, $sql, $params);

if (!$insertResult) {
    echo json_encode(['status' => 'error', 'message' => 'Neuspješno unošenje tokena u bazu']);
    exit;
}

$mail = new PHPMailer(true);
try {
    // SMTP server settings – update these with your actual settings
    $mail->isSMTP();
    $mail->Host       = 'mail.tsck.eu';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'skolskikurikul@tsck.eu';
    $mail->Password   = 'G-L86RZPy_J$';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet = 'UTF-8';

    // Email metadata – update the sender email as needed
    $mail->setFrom('skolskikurikul@tsck.eu', 'Školski kurikul');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Zahtjev za resetiranje lozinke';
    $mail->Body    = "
        <h2>Zahtjev za resetiranje lozinke</h2>
        <p>Poštovani, zaprimili smo zahtjev za resetiranje lozinke. Zahtjev je valjan 30 minuta.</p>
        <p><strong>Korisničko ime:</strong> {$email}</p>
        <a href={$resetURL}>Pritisnite za resitiranje lozinke</a>
        <p>Ako niste vi poslali zahtjev, molimo zanemarite ovu poruku.</p>
    ";
    $mail->AltBody = "Reset lozinke za korisnika: {$email}";

    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'Zahtjev za resetiranje lozinke je uspješno poslan',
        'token' => $token
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Greška pri slanju: ' . $mail->ErrorInfo
    ]);
}

function generateToken($length = 50)
{
    if ($length % 2 !== 0) {
        throw new \Exception("Token length must be an even number.");
    }

    while (true) {
        try {
            return bin2hex(random_bytes($length / 2));
        } catch (\Exception $e) {
            // Optionally, log the error before retrying
        }
    }
}
