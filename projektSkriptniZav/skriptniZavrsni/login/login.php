<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <script src="login.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Login</title>
</head>

<body>
    <div class="login-container">
        <img src=".\logo\tsck.png" alt="Logo" class="logo">

        <div class="form-group">
            <label for="username">Korisničko ime:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Lozinka:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="button" onclick="logInCheck()">LOGIN</button>
        <button id="resetButton" onclick="passwordReset()">Reset Password</button>


    </div>
</body>

</html>