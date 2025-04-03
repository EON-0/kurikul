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

function passwordReset() {
    // Get the username value (should be an email)
    let username = document.getElementById("username").value;

    if (username.length == 0) {
        alert("Za resetiranje lozinke potrebno je korisničko ime!");
        return;
    }

    $.ajax({
        url: "mail.php",  // Path to your PHP file
        method: "POST",
        data: { username: username },
        dataType: "json",
        success: function(data) {
            if (data.status === "success") {
                alert("Zahtjev za promjenu lozinke je uspješno poslan");
            } else {
                alert("Neuspješno slanje zahtjeva za promjenu lozinke: " + data.message);
            }
        },
        error: function() {
            alert("Došlo je do pogreške pri slanju zahtjeva za promjenu lozinke");
        }
    });
}


function setLoggedInSession() {
    $.ajax({
        url: 'set_session.php',
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

function spremiResetiranuLozinku(){
    let password1 = document.getElementById("lozinka1");
    let password2 = document.getElementById("lozinka2");

    if(password1.value != password2.value){
        alert("Lozinke se ne podudaraju.");
        return;
    }

    $.ajax({
        url: 'spremiResetiranuLozinku.php',
        method: 'POST',
        data: { password: password1.value, userID: userID },
        dataType: 'json',
        success: function(status) {
            alert("Lozinka je uspjesno spremljena.")
        },
        error: function() {
          alert('Došlo je do pogreške prilikom resetiranja lozinke.');
        }

    })
}