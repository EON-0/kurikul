// Event listeners for buttons

document.addEventListener('DOMContentLoaded', function () {


    const saveButton = document.getElementById('save-button');
    if (saveButton) {
        saveButton.addEventListener('click', function (event) {
            event.preventDefault();
            Save();
            window.location.reload();
        });
    }

    const deleteButton = document.getElementById('delete-button');
    if (deleteButton) {
        deleteButton.addEventListener('click', function (event) {
            event.preventDefault();
            obrisiAktivnost();
            window.location.reload();
        });
    }

    const cancelButton = document.getElementById('cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', function (event) {
            event.preventDefault();
            clearForm();
            window.location.reload();
        });
    }

    const confirmButton = document.getElementById('confirm-button');
    if (confirmButton) {
        confirmButton.addEventListener('click', function (event) {
            event.preventDefault();
            acceptReport();
        });
    }

    const kopirajButton = document.getElementById('kopiraj');
    if (kopirajButton) {
        kopirajButton.addEventListener('click', function (event) {
            event.preventDefault();
            getStatic();
            appState.novaAktivnost = true;
            appState.globalID = -1;
            alert("Kopirali ste prethodno odabranu aktivnost.\nZa spremanje nove kopirane aktivnosti pritisnite SAVE");
        });
    }

    const createDocButton = document.getElementById('createDocButton');
    if (createDocButton) {
        createDocButton.addEventListener('click', function (event) {
            event.preventDefault();
            sendDataAndThenRedirect();
        });
    }


    // Generic add/remove/edit handler setup
    function setupRadioFieldHandlers(addId, removeId, editId, containerId, promptText) {
        const addButton = document.getElementById(addId);
        const removeButton = document.getElementById(removeId);
        const editButton = document.getElementById(editId);
        const container = document.getElementById(containerId);

        addButton.addEventListener("click", function () {
            const text = prompt(promptText);
            if (text) {
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.id = `${containerId}-0`;
                radio.name = containerId;
                radio.value = "0";

                const label = document.createElement('label');
                label.htmlFor = radio.id;
                label.textContent = text;

                container.appendChild(radio);
                container.appendChild(label);
            }
        });

        removeButton.addEventListener("click", function () {
            const selectedRadio = document.querySelector(`input[name="${containerId}"]:checked`);
            if (selectedRadio) {
                const label = selectedRadio.nextSibling;
                selectedRadio.remove();
                if (label && label.tagName === "LABEL") {
                    label.remove();
                }
            } else {
                alert("Odaberite stavku za brisanje!");
            }
        });

        editButton.addEventListener("click", function () {
            const selectedRadio = document.querySelector(`input[name="${containerId}"]:checked`);
            if (selectedRadio) {
                const newText = prompt("Unesite novi tekst:", selectedRadio.nextSibling.textContent);
                if (newText) {
                    selectedRadio.nextSibling.textContent = newText;
                }
            } else {
                alert("Odaberite stavku za uređivanje!");
            }
        });
    }



    // Apply to all sections
    setupRadioFieldHandlers("add-goal", "remove-goal", "edit-goal", "list-goals", "Unesite cilj:");
    setupRadioFieldHandlers("add-realization", "remove-realization", "edit-realization", "list-realizations", "Unesite način realizacije:");
    setupRadioFieldHandlers("add-evaluation", "remove-evaluation", "edit-evaluation", "list-evaluation", "Unesite način vrednovanja:");
    setupRadioFieldHandlers("add-expense", "remove-expense", "edit-expense", "expenses", "Unesite trošak:");

    const godinaCheckbox = document.getElementById("aktualna-godina");
    if (godinaCheckbox) {
        godinaCheckbox.addEventListener("change", function (event) {
            appState.trenutnaGodina = event.target.checked ? 1 : -1;
            getAktinosti(user_ID);
        });
    }
});
