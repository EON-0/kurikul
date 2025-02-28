$(document).ready(function() {
    getDropDown();
    getAktivnosti(user_ID);

    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    //event listiner za promjene dropdown menia
    vrstaAktivnostiDropDown.addEventListener('change', function() {
    let odabir = vrstaAktivnostiDropDown.value || -1;
    getAktivnosti(user_ID);
    });

});

function getAktivnosti(user_ID) {
    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    let odabir = vrstaAktivnostiDropDown.value || -1;

    let aktivnostiContainer = document.getElementById('scrollable-div');
    aktivnostiContainer.innerHTML = '';
    
    $.ajax({
        url: "getAktinosti.php", 
        method: "GET",
        data: { user_ID: user_ID, odabir: odabir},
        dataType: "JSON",
        success: function(aktivnosti_array) {
            let scrollabl_div = $('#scrollable-div'); 
            aktivnosti_array.forEach(function(aktivnost) {
                let button = $('<button>')
                .attr('id', 'ID_aktivnosti')
                .attr('class', 'ID_aktivnosti')
                .attr('value', aktivnost.ID)
                .text(aktivnost.Naziv);

                scrollabl_div.append(button);
            });
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja Aktivnosti");
        }
    });
}

function getDropDown() {
    $.ajax({
        url: "getDropDown.php", 
        method: "GET",
        dataType: "JSON",
        success: function(dropDowns) {
            if (Array.isArray(dropDowns) && dropDowns.length === 2) {
                let vrstaAktivnosti = dropDowns[0];
                let statusi = dropDowns[1];

                // Populate vrsta aktivnosti select
                let activitySelect = document.getElementById('activity-type');
                let activitySelect_left = document.getElementById('vrste-aktivnosti'); // drop pown iznad popisa aktivnosti

                activitySelect.innerHTML = '<option value="-1" disabled selected>Odaberi vrstu aktivnosti</option>';
                activitySelect_left.innerHTML = '<option value="-1" disabled selected>Odaberi vrstu aktivnosti</option>';

                vrstaAktivnosti.forEach(function(item) {
                     // Create a new option for each select
                    let option1 = document.createElement('option');
                    option1.value = item.ID;
                    option1.textContent = item.Naziv;
                    activitySelect.appendChild(option1);

                    let option2 = document.createElement('option');
                    option2.value = item.ID;
                    option2.textContent = item.Naziv;
                    activitySelect_left.appendChild(option2);
                });

                // Populate statusi select
                let statusSelect = document.getElementById('status');
                statusSelect.innerHTML = '<option value="" disabled selected>Odaberi status</option>';
                statusi.forEach(function(item) {
                    let option = document.createElement('option');
                    option.value = item.ID;
                    option.textContent = item.Status;
                    statusSelect.appendChild(option);
                });

            } else {
                console.error("Unexpected JSON structure:", dropDowns);
            }
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja Dropdowna");
        }
    });
}
function logOut() {
    var cookies = document.cookie.split(";");

    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        var equalsPos = cookie.indexOf("=");
        var name = equalsPos > -1 ? cookie.substr(0, equalsPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
    }
    window.location.href = 'delete_session.php';
}

