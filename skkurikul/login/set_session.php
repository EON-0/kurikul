<?php
session_start();
if (isset($_POST['action']) && $_POST['action'] == 'set_loggedin') {
    $_SESSION['loggedin'] = true;
    echo json_encode(['status' => 'success', 'message' => 'Session set to logged in']);
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
}
?>
