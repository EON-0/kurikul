<?php
// provjerava koliko je vremena prošlo od posljednjeg logina
session_start();
function checkSessionTime(){
    if (isset($_SESSION['login_time'])) {
        $timeout_duration = 3600;
        // Check if session has expired
        if (time() - $_SESSION['login_time'] > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: ../login/login.php");
            exit();
        }
    } else {
        // Set the session start time
        $_SESSION['login_time'] = time();
    }
}


?>