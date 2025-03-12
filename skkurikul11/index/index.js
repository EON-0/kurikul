//klasa za lakse mjenjanje i dobivanje vrijednosti trenutne aktivnosti
//
class Globla_ID {

    constructor() {
        //0 => nova aktivnost
        this.value = 0;
    }

    putValue(val) {
        this.value = val;
    }

    getValue() {
        return this.value;
    }
}

//globalan varijabla
let globalID = new Globla_ID();
let novaAktivnost = true;

$(document).ready(function () {
    getStatic();
    getAktinosti(user_ID);
    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    //event listiner za promjene dropdown menia
    vrstaAktivnostiDropDown.addEventListener('change', function () {
        let odabir = vrstaAktivnostiDropDown.value || -1;
        getAktinosti(user_ID);
    });
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

function getAktinosti(user_ID) {
    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    let odabir = vrstaAktivnostiDropDown.value || -1;

    let aktivnostiContainer = document.getElementById('scrollable-div');
    aktivnostiContainer.innerHTML = '';

    $.ajax({
        url: "getAktinosti.php",
        method: "POST",
        data: { user_ID: user_ID, odabir: odabir },
        dataType: "JSON",
        success: function (aktivnosti_array) {
            let scrollabl_div = $('#scrollable-div');
            aktivnosti_array.forEach(function (aktivnost) {
                let button = $('<button>')
                    .attr('id', 'ID_aktivnosti')
                    .attr('class', 'ID_aktivnosti')
                    .attr('value', aktivnost.ID)
                    .attr('onclick', 'getAktinostPodaci(this)')
                    .text(aktivnost.Naziv);

                scrollabl_div.append(button);
            });
        },
        error: function () {
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
        success: function (staticData) {


            if (Array.isArray(staticData) && staticData.length === 3) {
                let vrstaAktivnosti = staticData[0];
                let statusi = staticData[1];
                let korisnici = staticData[2];

                addActivityType(vrstaAktivnosti);
                addStatus(statusi);
                addCheckboxes(korisnici);


                //postavi datum i autora -> to se ne mijenja
                const createdInput = document.getElementById('created');
                const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
                createdInput.value = today;
                document.getElementById('author').value = fullName;

            } else {
                console.error("Unexpected JSON structure:", dropDowns);
            }
        },
        error: function () {
            alert("Došlo je do pogreške prilikom dohvačanja Dropdowna");
        }
    });
}
function addStatus(array) {
    let statusSelect = document.getElementById('status');
    statusSelect.innerHTML = '<option value="" disabled selected>Odaberi status</option>';
    array.forEach(function (item) {
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
    array.forEach(function (item) {
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



function getAktinostPodaci(button) {
    let aktivnost_ID = button.value;
    globalID.putValue(aktivnost_ID);
    novaAktivnost = false;

    $.ajax({
        url: "getDataAktivnost.php",
        method: "GET",
        data: { aktivnost_ID: globalID.getValue() },
        dataType: "JSON",
        success: function (aktivnost_podaci) {
            //console.log(aktivnost_podaci);//debug 

            let opci_podaci = aktivnost_podaci[0];
            popuniOpce(opci_podaci);

            let ciljevi = aktivnost_podaci[1];
            popuniCiljevi(ciljevi);

            let troskovnik = aktivnost_podaci[2];
            popuniTroskovnik(troskovnik);

            let relizacija = aktivnost_podaci[3];
            popuniRealizaciju(relizacija);

            let vrednovanje = aktivnost_podaci[4];
            popuniVrednovanje(vrednovanje);


            let nositelji = Array.isArray(aktivnost_podaci[5]) ? aktivnost_podaci[5].map(obj => obj.ID) : [];
            checkCheckboxes(nositelji);
        },
        error: function () {
            alert("Došlo je do pogreške prilikom dohvačanja podataka");
        }
    })
}


function popuniVrednovanje(vrednovanje) {
    let evaluationDiv = document.getElementById('list-evaluation');
    evaluationDiv.innerHTML = ''; // Clear previous content

    //nacin realizacije -> nacin
    vrednovanje.forEach(vrijednost => {
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `evaluation-${vrijednost.ID}`;
        radio.name = 'evaluation'; // Ensure all radios share the same name for single selection
        radio.value = vrijednost.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = vrijednost.Vrednovanje;

        evaluationDiv.appendChild(radio);
        evaluationDiv.appendChild(label);
        // goalsDiv.appendChild(document.createElement('br')); // Optional line break
    });
}

//radio buttoni za troskovnik
function popuniTroskovnik(troskovnik) {
    let goalsDiv = document.getElementById('expenses');
    goalsDiv.innerHTML = ''; // Clear previous content

    troskovnik.forEach(trosak => {
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `expense-${trosak.ID}`;
        radio.name = 'expense'; // Ensure single selection
        radio.value = trosak.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = trosak.Trosak;

        goalsDiv.appendChild(radio);
        goalsDiv.appendChild(label);
        // goalsDiv.appendChild(document.createElement('br')); // Optional line break
    });
}

function popuniRealizaciju(relizacija) {
    let goalsDiv = document.getElementById('list-realizations');
    goalsDiv.innerHTML = ''; // Clear previous content

    //nacin realizacije -> nacin
    relizacija.forEach(nacin => {
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `realization-${nacin.ID}`;
        radio.name = 'realization'; // Ensure all radios share the same name for single selection
        radio.value = nacin.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = nacin.Realizacija;

        goalsDiv.appendChild(radio);
        goalsDiv.appendChild(label);
        // goalsDiv.appendChild(document.createElement('br')); // Optional line break
    });
}



//radio buttoni za ciljeve
function popuniCiljevi(ciljevi) {
    let goalsDiv = document.getElementById('list-goals');
    goalsDiv.innerHTML = ''; // Clear previous content

    ciljevi.forEach(cilj => {
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `goal-${cilj.ID}`;
        radio.name = 'goal'; // Ensure all radios share the same name for single selection
        radio.value = cilj.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = cilj.Cilj;

        goalsDiv.appendChild(radio);
        goalsDiv.appendChild(label);
        // goalsDiv.appendChild(document.createElement('br')); // Optional line break
    });
}


function popuniOpce(opci_podaci) {

    const fields = {
        'name': opci_podaci["Naziv"],
        'created': opci_podaci["Kreirano"]?.split(' ')[0] || '',
        //'author': opci_podaci["Autor"], // dodaje se staticki
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

//alternativa je ka posle ajaxa do baze i onda mu on vrne id od najslcinijega 
//za zbrisati
function scrolldiv() {
    var elem = document
        .getElementByText("Markus Pilić");
    elem
        .scrollIntoView();
}


function Save() {
    let opciPodaci = putOpciPodaci();
    let nositelji = putCheckedCarriers();
    let ciljevi = putGoals();
    let realizacije = putRealizations();
    let troskovnik = putExpenses();
    let vrednovanje = putEvaluation();

    //za debug
    console.log(opciPodaci);
    console.log(nositelji);
    console.log(ciljevi);
    console.log(realizacije);
    console.log(troskovnik);
    console.log(vrednovanje);
    console.log("Aktivnost ID:", globalID.getValue());

    $.ajax({
        url: "saveAktivnost.php",
        method: "GET",
        data: {
            user_ID: user_ID,
            aktivnost_ID: globalID.getValue(),
            opciPodaci: opciPodaci,
            nositelji: nositelji,
            ciljevi: ciljevi,
            realizacije: realizacije,
            troskovnik: troskovnik,
            vrednovanje: vrednovanje
        },
        dataType: "JSON",
        success: function (stanje) {
            getAktivnosti(user_ID);
            alert(stanje["pravo"]);
        },
        error: function () {
            alert("Došlo je do pogreške prilikom spremanja Aktivnosti");
        }
    });
}

function putOpciPodaci() {
    const fields = [
        'name',
        'created',
        'author',
        'activity-type',
        'status',
        'purpose',
        'carriers',
        'responsibility',
        'timeline',
        'evaluation',
        'expenses',
        'report'
    ];

    const opci_podaci = {};

    fields.forEach(id => {
        const element = document.getElementById(id);
        opci_podaci[id] = element ? element.value : '';
    });
    return opci_podaci;
}

function putCheckedCarriers() {
    const checkedCarriers = [];
    const checkboxes = document.querySelectorAll('#carriers input[type="checkbox"]:checked');

    checkboxes.forEach(checkbox => {
        checkedCarriers.push(checkbox.value);
    });

    console.log('Checked Carriers:', checkedCarriers);
    return checkedCarriers;
}

function putGoals() {
    const allGoals = [];
    const radios = document.querySelectorAll('#list-goals input[type="radio"]');

    radios.forEach(radio => {
        const label = radio.parentElement.querySelector('label'); // Find the label associated with the radio button
        allGoals.push({
            value: radio.value,
            text: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });

    return allGoals;
}

function putRealizations() {
    const realizations = [];
    const radios = document.querySelectorAll('#list-realizations input[type="radio"]');

    radios.forEach(radio => {
        const label = radio.parentElement.querySelector('label'); // Find the label associated with the radio button
        realizations.push({
            value: radio.value,
            text: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });

    return realizations;

}
function putExpenses() {
    const expenses = [];
    const radios = document.querySelectorAll('#expenses input[type="radio"]');

    radios.forEach(radio => {
        const label = radio.parentElement.querySelector('label'); // Find the label associated with the radio button
        expenses.push({
            value: radio.value,
            text: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });

    return expenses;
}

function putEvaluation() {
    const evaluations = [];
    const radios = document.querySelectorAll('#list-evaluation input[type="radio"]');

    radios.forEach(radio => {
        const label = radio.parentElement.querySelector('label'); // Find the label associated with the radio button
        evaluations.push({
            value: radio.value,
            text: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });

    return evaluations;

}


//nova aktivnost
function vnost() {
    clearForm();
    novaAktivnost = true;
    alert("Za unos nove aktivnosti potreban je naziv,autor i kreirano.")
}
function clearForm() {
    const form = document.getElementById('aktivnost-form');

    form.querySelectorAll('input[type="text"], input[type="date"], textarea').forEach(input => {
        input.value = '';
    });

    form.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });
    form.querySelectorAll('.list-box').forEach(listBox => {
        listBox.innerHTML = '';
    });
    getStatic(fullName);



}

