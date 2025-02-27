<?php
include '../dbasse_local.php';
include 'password_hash.php';
session_start();

$response = [];

if(isset($_GET["username"]) && isset($_GET["password"])) {
    $username = $_GET["username"];
    $password = $_GET["password"];

    // SQL query to fetch the hashed password
    $sql = "SELECT * FROM sk_korisnici WHERE Username = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        $hashed_password = $row['Password'];

        // Check if the password matches
        if(checkPassword($password, $hashed_password)) {
            $_SESSION['username'] = $username;
            $_SESSION['FullName'] = $row['FullName'];
            $_SESSION['user_ID'] = $row['ID']; 
            $response['status'] = "success";
        } else {
            $response['status'] = "fail";
        }
    } else {
        $response['status'] = "fail";
    }

    $stmt->close();
    $con->close();
} else {
    $response['status'] = "fail";
}

echo json_encode($response);
?>
