<?php
include '../dbasse_conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["userID"]) || !isset($_POST["password"])) {
        echo json_encode(["status" => "error", "message" => "Nedostaju podaci."]);
        exit;
    }

    $userID = $_POST["userID"];
    $password = $_POST["password"];

    $password_md5 = md5($password);

    $sql = "UPDATE sk_korisnici SET sk_korisnici.Password = ? WHERE sk_korisnici.ID = ?";
    $params = [$password_md5, $userID];

    placeToDataBase($con, $sql, $params);

    $status["status"] = "Uspješna promijena lozinke!";
    echo json_encode($status, JSON_UNESCAPED_UNICODE);
}
