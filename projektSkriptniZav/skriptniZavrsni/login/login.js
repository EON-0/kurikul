function logInCheck() {
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    if (username.length == 0 || password.length == 0) {
        alert("Nedostaje korisničko ime ili lozinka!");
        return;
    }

    $.ajax({
        url: "logInCheck.php",
        method: "GET",
        data: { username: username, password: password },
        dataType: "JSON",
        success: function(data) {
            if (data.status === "success") {
                setCookie("korisnicko_Ime", username, 7); //keksici 
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
    let username = document.getElementById("username").value;

    if (username.length == 0) {
        alert("Za resetiranje lozinke potrebno je korisničko ime!");
        return;
    }

    $.ajax({
        url: "mail.php",
        method: "POST",
        data: { username: username },
        dataType: "json",
        success: function(data) {
            if (data.status === "success") {
                alert(data.message || "Zahtjev za promjenu lozinke je uspješno poslan");
            } else {
                alert(data.message || "Došlo je do pogreške pri slanju zahtjeva");
            }
        },
        error: function(xhr) {
            let errorMsg = "Došlo je do pogreške pri slanju zahtjeva";
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMsg += ": " + response.message;
            } catch(e) {
                errorMsg += " (server error)";
            }
            alert(errorMsg);
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
        data: { password: password1.value, userID: userID, token: token},
        dataType: 'json',
        success: function(status) {
            alert(status["message"]);
            window.location.href = "./login.php";
        },
        error: function() {
          alert('Došlo je do pogreške prilikom resetiranja lozinke.');
        }

    })
}
function setCookie(cname, cvalue, exdays) { //keksici 
    let d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }