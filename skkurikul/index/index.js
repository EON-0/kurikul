$(document).ready(function() {
    getStatic();
    getAktivnosti(user_ID);

    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    //event listiner za promjene dropdown menia
    vrstaAktivnostiDropDown.addEventListener('change', function() {
    let odabir = vrstaAktivnostiDropDown.value || -1;
    getAktivnosti(user_ID);
    });

    /*

    // trebalo bi dobro delati dok ubacim u geAktiviti
    setTimeout(() => {
        const ids = [1, 1024];
        checkCheckboxes(ids);
    }, 500); // Adjust timing based on your data loading speed

    */
});

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
                .attr('onclick', 'getAktinostPodaci(this)')
                .text(aktivnost.Naziv);

                scrollabl_div.append(button);
            });
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja Aktivnosti");
        }
    });
}

//podaci koji se uvjek nalaze na webstranici; dropdown i korsinici
function getStatic() {
    $.ajax({
        url: "getStatic.php", 
        method: "GET",
        dataType: "JSON",
        success: function(staticData) { 
            if (Array.isArray(staticData) && staticData.length === 3) {
                let vrstaAktivnosti = staticData[0];
                let statusi = staticData[1];
                let korisnici = staticData[2];

                addActivityType(vrstaAktivnosti);
                addStatus(statusi);
                addCheckboxes(korisnici);
        
            } else {
                console.error("Unexpected JSON structure:", dropDowns);
            }
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja Dropdowna");
        }
    });
}
function addStatus(array){
    let statusSelect = document.getElementById('status');
    statusSelect.innerHTML = '<option value="" disabled selected>Odaberi status</option>';    
    array.forEach(function(item) {
        let option = document.createElement('option');
        option.value = item.ID;
        option.textContent = item.Status;
        statusSelect.appendChild(option);
    });

}

function addActivityType(array) {
    let activitySelect = document.getElementById('activity-type');
    let activitySelect_left = document.getElementById('vrste-aktivnosti');

    activitySelect.innerHTML = ''; // Clear previous options
    activitySelect_left.innerHTML = ''; // Clear previous options

    activitySelect.innerHTML = '<option value="-1" disabled selected>Odaberi vrstu aktivnosti</option>';
    activitySelect_left.innerHTML = '<option value="-1" disabled selected>Odaberi vrstu aktivnosti</option>';

    // Add activity options
    array.forEach(function(item) {
        let option1 = document.createElement('option');
        option1.value = item.ID;
        option1.textContent = item.Naziv;
        activitySelect.appendChild(option1);

        let option2 = document.createElement('option');
        option2.value = item.ID;
        option2.textContent = item.Naziv;
        activitySelect_left.appendChild(option2);
    });
}

//dodaje chcekboxe
function addCheckboxes(array) {
    const carriersDiv = document.getElementById('carriers');
    carriersDiv.innerHTML = ''; // Clear previous checkboxes if needed

    array.forEach(item => {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `carrier-${item.ID}`;
        checkbox.name = 'carrier';
        checkbox.value = item.ID;

        const label = document.createElement('label');
        label.htmlFor = checkbox.id;
        label.textContent = item.FullName;

        carriersDiv.appendChild(checkbox);
        carriersDiv.appendChild(label);
        carriersDiv.appendChild(document.createElement('br')); // Optional line break
    });
}



function getAktinostPodaci(button){
    let aktivnost_ID =  button.value;
    $.ajax({
        url: "getDataAktivnost.php",
        method: "GET",
        data: {aktivnost_ID: aktivnost_ID },
        dataType: "JSON",
        success: function(aktivnost_podaci){
            let opci_podaci = aktivnost_podaci[0]; 
            popuniOpce(opci_podaci);



            let nositelji = aktivnost_podaci[5].map(obj => obj.ID); // Extract IDs
            checkCheckboxes(nositelji);
        },
        error: function() {
            alert("Došlo je do pogreške prilikom dohvačanja podataka");
        } 
    })
}

function popuniOpce(opci_podaci) {
    const fields = {  
        'name': opci_podaci["Naziv"],
        'created': opci_podaci["Kreirano"]?.split(' ')[0] || '',
        'author': opci_podaci["Autor"],
        'activity-type': opci_podaci["Vrsta_Aktivnosti"],
        'status': opci_podaci["Status"],
        'purpose': opci_podaci["Namjena"],
        'carriers': opci_podaci["Naziv"],
        'responsibility': "Neznam kaj je to",
        'timeline': opci_podaci["Vremenik"],
        'evaluation': opci_podaci["Naziv"],
        'expenses': opci_podaci["Naziv"],
        'report': opci_podaci["Izvjesce"]
    };

    for (const [id, value] of Object.entries(fields)) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || ''; // Set to empty string if value is null/undefined
        }
    }
}


//postavi checka nositelje
function checkCheckboxes(ids) {
    ids.forEach(id => {
        const checkbox = document.querySelector(`#carrier-${id}`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}



