$(document).ready(function() {
    getAktivnosti(user_ID);
});

function getAktivnosti(user_ID) {
    $.ajax({
        url: "getAktinosti.php", 
        method: "GET",
        data: { user_ID: user_ID },
        dataType: "JSON",
        success: function(aktivnosti_array) {
            let scrollabl_div = $('#scrollable-div'); 
            aktivnosti_array.forEach(function(aktivnost) {
                let button = $('<button>').attr('value', aktivnost.ID).text(aktivnost.Naziv);
                scrollabl_div.append(button);
            });
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja Aktivnosti");
        }
    });
}

