function logInCheck() {
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    if (username.length == 0 || password.length == 0) {
        alert("Nedostaje korisničko ime ili lozinka!");
        return;
    }

    $.ajax({
        url: "logInCheck.php",  // Ensure the PHP file path is correct
        method: "GET",
        data: { username: username, password: password },
        dataType: "JSON",
        success: function(data) {
            if (data.status === "success") {
                setLoggedInSession();
                window.location.href = "../index/index.php";
            } else {
                alert("Neuspješno prijavljivanje!");
            }
        },
        error: function() {
            alert("Došlo je do pogreške prilikom obrade vaše zahtjeva.");
        }
    });
}
function setLoggedInSession() {
    $.ajax({
        url: 'set_session.php',  // Make sure the path is correct
        method: 'POST',
        data: { action: 'set_loggedin' },
        dataType: 'JSON',
        success: function(response) {
        },
        error: function() {
            alert('There was an error setting the session.');
        }
    });
}
