//globalan varijabla
let appState = {
    novaAktivnost: true,
    globalID: -1,
    trenutnaGodina: -1
};

$(document).ready(function () {
    getStatic();
    getAktinosti(user_ID);

    let vrstaAktivnostiDropDown = document.getElementById('vrste-aktivnosti');
    // Event listener for changes in the dropdown menu
    vrstaAktivnostiDropDown.addEventListener('change', function () {
        let odabir = vrstaAktivnostiDropDown.value || -1;
        getAktinosti(user_ID);
    });

    // activity search
    $("#search-activitys").on("keyup", function () {
        var query = $(this).val().toLowerCase();

        // Loop through each button in the scrollable div and toggle visibility based on the query match
        $("#scrollable-div button").each(function () {
            // Skip buttons that are toggled off
            if ($(this).hasClass("toggled-off")) {
                return;
            }
            var buttonText = $(this).text().toLowerCase();
            $(this).toggle(buttonText.indexOf(query) > -1);
        });
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
        data: { user_ID: user_ID, odabir: odabir, aktualnaGodina: appState.trenutnaGodina },
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
            alert("Došlo je do pogreške prilikom dohvačanja aktivnosti");
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
                document.getElementById("search-carriers").addEventListener("input", function () {
                    const searchValue = this.value.toLowerCase();
                    const carriersContainer = document.getElementById("carriers");
                    // Dohvati sve checkboxove unutar kontejnera
                    const checkboxes = carriersContainer.querySelectorAll("input[type='checkbox']");

                    checkboxes.forEach(checkbox => {
                        // Pronađi pripadajuću labelu koristeći atribut "for"
                        const label = carriersContainer.querySelector(`label[for="${checkbox.id}"]`);
                        const labelText = label ? label.textContent.toLowerCase() : "";
                        const isMatch = labelText.includes(searchValue);

                        // Prikaži ili sakrij checkbox i labelu
                        checkbox.style.display = isMatch ? "inline-block" : "none";
                        if (label) {
                            label.style.display = isMatch ? "inline-block" : "none";
                            // Sakrij i <br> ako postoji odmah nakon labele
                            const br = label.nextElementSibling;
                            if (br && br.tagName.toLowerCase() === "br") {
                                br.style.display = isMatch ? "block" : "none";
                            }
                        }
                    });
                });

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
    activitySelect_left.innerHTML = '<option value="-1"  selected>Sve aktivnost</option>';

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
        carriersDiv.appendChild(document.createElement('br'));
    });
}

function getAktinostPodaci(button) {
    const buttons = document.querySelectorAll(".ID_aktivnosti");
    buttons.forEach(btn => {
        btn.classList.remove("selected");
    });

    // Add the 'selected' class to the clicked button
    button.classList.add("selected");

    let aktivnost_ID = button.value;
    appState.globalID = aktivnost_ID;
    appState.novaAktivnost = false;
    console.log(appState.novaAktivnost);

    $.ajax({
        url: "getDataAktivnost.php",
        method: "GET",
        data: { aktivnost_ID: appState.globalID },
        dataType: "JSON",
        success: function (aktivnost_podaci) {
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

function getAktinostPodaciWithID(){
    appState.novaAktivnost = false;

    $.ajax({
        url: "getDataAktivnost.php",
        method: "GET",
        data: { aktivnost_ID: appState.globalID },
        dataType: "JSON",
        success: function (aktivnost_podaci) {
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
        radio.name = 'evaluation';
        radio.value = vrijednost.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = vrijednost.Vrednovanje;
        evaluationDiv.appendChild(radio);
        evaluationDiv.appendChild(label);
        evaluationDiv.appendChild(document.createElement('br'));
    });
}

//radio buttoni za troskovnik
function popuniTroskovnik(troskovnik) {
    let expensesDiv = document.getElementById('expenses');
    expensesDiv.innerHTML = ''; // Clear previous content

    troskovnik.forEach(trosak => {
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `expense-${trosak.ID}`;
        radio.name = 'expense'; // Ensure single selection
        radio.value = trosak.ID;

        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = trosak.Trosak;

        expensesDiv.appendChild(radio);
        expensesDiv.appendChild(label);
        expensesDiv.appendChild(document.createElement('br')); // Optional line break
    });
}

function popuniRealizaciju(relizacija) {
    let realizationsDiv = document.getElementById('list-realizations');
    realizationsDiv.innerHTML = ''; // Clear previous content

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

        realizationsDiv.appendChild(radio);
        realizationsDiv.appendChild(label);
        realizationsDiv.appendChild(document.createElement('br')); // Optional line break
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
        goalsDiv.appendChild(document.createElement('br')); // Optional line break
    });
}

function popuniOpce(opci_podaci) {
    const fields = {
        'name': opci_podaci["Naziv"],
        'created': opci_podaci["Kreirano"]?.split(' ')[0] || '',
        'author': opci_podaci["Autor"], //statici se dodaje trenutni korisnik, ovo je za tuđe aktivnosti
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
            element.value = value || '';
        }
    }

    if(opci_podaci["potvrdeno"]==1){
        document.querySelector('.right-panel').style.backgroundColor = '#faf6cf';
        document.querySelector('.left-panel').style.backgroundColor = '#faf6cf';
    }
    else{
        document.querySelector('.right-panel').style.backgroundColor = '#ffffff';
        document.querySelector('.left-panel').style.backgroundColor = '#ffffff';

    }


}

//postavi checka nositelje
function checkCheckboxes(ids) {
    var checkboxes = carriers.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function (checkbox) {
        checkbox.checked = false;
    });

    ids.forEach(id => {
        const checkbox = document.querySelector(`#carrier-${id}`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

//alternativa je ka posle ajaxa do baze i onda mu on vrne id od najslcinijega 
//za zbrisati
function Save() {
    let opciPodaci = putOpciPodaci();
    let nositelji = putCheckedCarriers();
    let ciljevi = putGoals();
    let realizacije = putRealizations();
    let troskovnik = putExpenses();
    let vrednovanje = putEvaluation();

    // Debug output
    console.log(opciPodaci);
    console.log(nositelji);
    console.log(ciljevi);
    console.log(realizacije);
    console.log(troskovnik);
    console.log(vrednovanje);
    console.log("Aktivnost ID:", appState.globalID);
    console.log(appState.novaAktivnost);
    
    let php_url;
    let da;
    if (appState.novaAktivnost) {
        da = confirm("Kreiraj novu aktivnost?");
        php_url = "novaAktivnost.php";
    } else {
        da = confirm("Spremi promjene?");
        php_url = "save.php";
    }
    if (da) {
        
        $.ajax({
            url: php_url,
            method: "POST",
            data: {
                user_ID: user_ID,
                aktivnost_ID: appState.globalID,
                opciPodaci: opciPodaci,
                nositelji: nositelji,
                ciljevi: ciljevi,
                realizacije: realizacije,
                troskovnik: troskovnik,
                vrednovanje: vrednovanje
            },
            dataType: "JSON",
            success: function (odgovor) {
                alert(odgovor["pravo"]);
            },
            error: function () {
                alert("Došlo je do pogreške prilikom spremanja aktivnosti");
            }
        });

    }
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
    const radios = document.querySelectorAll('input[name="goal"]');

    radios.forEach(radio => {
        // Assumes the label is directly after the radio input
        const label = radio.nextElementSibling;
        allGoals.push({
            ID: radio.value,
            cilj: label ? label.textContent.trim() : ''
        });
    });

    return allGoals;
}

function putRealizations() {
    const realizations = [];
    const radios = document.querySelectorAll('input[name="realization"]');

    radios.forEach(radio => {
        const label = radio.nextElementSibling; // Find the label associated with the radio button
        realizations.push({
            ID: radio.value,
            realizacija: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });
    return realizations;
}

function putExpenses() {
    const expenses = [];
    const radios = document.querySelectorAll('input[name="expense"]');

    radios.forEach(radio => {
        const label = radio.nextElementSibling; // Find the label associated with the radio button
        expenses.push({
            ID: radio.value,
            trosak: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });
    return expenses;
}

function putEvaluation() {
    const evaluations = [];
    const radios = document.querySelectorAll('input[name="evaluation"]');

    radios.forEach(radio => {
        const label = radio.nextElementSibling; // Find the label associated with the radio button
        evaluations.push({
            ID: radio.value,
            vrednovanje: label ? label.textContent.trim() : '' // Get text content of the label
        });
    });
    return evaluations;
}

//nova aktivnost
function kreiraAktivnost() {
    clearForm();
    appState.novaAktivnost = true;
    appState.globalID = -1;
    alert("Unesite podatke i pritisnite Spremi za kreiranje aktivnosti");
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
    getStatic();
}

function obrisiAktivnost() {
    if (appState.globalID == -1) {
        alert("Nije odabrana ni jedan aktivnost!");
    } else {
        if (confirm("Obrisi trenutnu aktivnost?")) {
            $.ajax({
                url: "deletAktivnost.php",
                method: "POST",
                data: {
                    user_ID: user_ID,
                    aktivnost_ID: appState.globalID
                },
                dataType: "JSON",
                success: function (odgovor) {
                    alert(odgovor["status"]);
                },
                error: function () {
                    alert("Došlo je do pogreške prilikom brisanja aktivnosti");
                }
            });
        }
    }
}

function acceptReport() {
    if (appState.globalID == -1) {
        alert("Nije odabrana ni jedan aktivnost!");
    } else {
        if (confirm("Potvrdite izvješće trenutne aktivnosti?")) {
            $.ajax({
                url: "acceptReport.php",
                method: "POST",
                data: {
                    user_ID: user_ID,
                    aktivnost_ID: appState.globalID

                },
                dataType: "JSON",
                success: function (odgovor) {
                    alert(odgovor["status"]);
                },
                error: function () {
                    alert("Došlo je do pogreške prilikom potvrđivanja izvješća");
                }
            });
        }
    }
    moveForward();
}

//za kretanje
function moveForward() {
    // Find the currently selected button
    const selectedButton = document.querySelector(".ID_aktivnosti.selected");
    let nextButton = null;
    if (selectedButton) {
        nextButton = selectedButton.nextElementSibling;
        // Loop until we find the next visible activity button or run out of siblings
        while (nextButton) {
            if (nextButton.classList.contains("ID_aktivnosti") && $(nextButton).is(":visible")) {
                break;
            }
            nextButton = nextButton.nextElementSibling;
        }
        if (nextButton) {
            nextButton.click();
            nextButton.focus();
            nextButton.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    } else {
        // If no button is selected, choose the first visible activity button
        const allButtons = document.querySelectorAll(".ID_aktivnosti");
        for (let btn of allButtons) {
            if ($(btn).is(":visible")) {
                btn.click();
                btn.focus();
                btn.scrollIntoView({ behavior: "smooth", block: "center" });
                break;
            }
        }
    }
}

function moveBackward() {
    // Find the currently selected button
    const selectedButton = document.querySelector(".ID_aktivnosti.selected");
    let prevButton = null;
    if (selectedButton) {
        prevButton = selectedButton.previousElementSibling;
        // Loop until we find the previous visible activity button or run out of siblings
        while (prevButton) {
            if (prevButton.classList.contains("ID_aktivnosti") && $(prevButton).is(":visible")) {
                break;
            }
            prevButton = prevButton.previousElementSibling;
        }
        if (prevButton) {
            prevButton.click();
            prevButton.focus();
            prevButton.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    } else {
        // If no button is selected, choose the last visible activity button
        const allButtons = document.querySelectorAll(".ID_aktivnosti");
        for (let i = allButtons.length - 1; i >= 0; i--) {
            if ($(allButtons[i]).is(":visible")) {
                allButtons[i].click();
                allButtons[i].focus();
                allButtons[i].scrollIntoView({ behavior: "smooth", block: "center" });
                break;
            }
        }
    }
}

function activateButtonByValue(buttonValue) {
    // Select all elements with the class "ID_aktivnosti"
    const buttons = document.querySelectorAll(".ID_aktivnosti");
    
    // Loop through each element to check for a matching value and visibility
    for (let button of buttons) {
        // Check if the element has the matching value attribute and is visible
        if (button.getAttribute("value") === buttonValue && $(button).is(":visible")) {
            // Simulate click, set focus, and scroll into view smoothly (centering it)
            button.click();
            button.focus();
            button.scrollIntoView({ behavior: "smooth", block: "center" });
            return; // Exit after activating the first matching button
        }
    }
    
    // Optional: Log a warning if no matching visible element was found
    console.warn(`No visible element found with value "${buttonValue}"`);
}  
function sendDataAndThenRedirect() {
   // const godinaDropdown = document.getElementById('godina');
    //const year = godinaDropdown.value; 
    window.location.href = "./makeDocument.php";
}



function podsjetnikNaOdmor() {
    alert("Vrijeme je za malo odmora");
    setTimeout(podsjetnikNaOdmor, 5 * 60 * 1000); // 5 minuta
}


function podaciOprozotru() {
    alert(
           "href: " + window.location.href +  "\n" + 
           "hostname: " + window.location.hostname + "\n" + 
           "pathname: " + window.location.pathname + "\n" + 
           "protocol: " + window.location.protocol + "\n"
    );
  }
  