<?php
include '../dbasse_conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["userID"]) || !isset($_POST["password"])) {
        echo json_encode(["status" => "error", "message" => "Nedostaju podaci."]);
        exit;
    }

    $userID = $_POST["userID"];
    $password = $_POST["password"];

    // Consider using password_hash for better security (MD5 is not secure)
    $password_md5 = md5($password);

    $sql = "UPDATE sk_korisnici SET Password = ? WHERE ID = ?";
    $params = [$password_md5, $userID];

    $updateResult = placeToDataBase($con, $sql, $params);

    if ($updateResult) {
        echo json_encode(["status" => "success", "message" => "Uspješno resetirana lozinka!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Greška pri ažuriranju lozinke."]);
    }
}
