
//event listineri za gumbe
// Event listener for the "Spremi" button
document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('save-button');
    if (saveButton) {
        saveButton.addEventListener('click', function(event) {
            event.preventDefault();  // Prevent the default form submission
            Save();
        });
    }

    // Event listener for the "Obriši" button
    const deleteButton = document.getElementById('delete-button');
    if (deleteButton) {
        deleteButton.addEventListener('click', function(event) {
            event.preventDefault();
            // Add your delete functionality here
        });
    }

    // Event listener for the "Odustani" button
    const cancelButton = document.getElementById('cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', function(event) {
            event.preventDefault();
            clearForm()
        });
    }

    // Event listener for the "Potvrdi" button
    const confirmButton = document.getElementById('confirm-button');
    if (confirmButton) {
        confirmButton.addEventListener('click', function(event) {
            event.preventDefault();
            // Add your confirm functionality here
        });
    }

    // Event listener for the "Odbaci" button (reset)
    const resetButton = document.getElementById('reset-button');
    if (resetButton) {
        resetButton.addEventListener('click', function(event) {
            event.preventDefault();
            // Add your reset functionality here
        });
    }

    //goal button
        // Get elements
        const addGoalButton = document.getElementById("add-goal");
        const removeGoalButton = document.getElementById("remove-goal");
        const editGoalButton = document.getElementById("edit-goal");
        const goalsDiv = document.getElementById("list-goals");
    
        addGoalButton.addEventListener("click", function () {
            const goalText = prompt("Unesite cilj:");
            if (goalText) {
                // Create radio button
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.id = `goal-0`; // Always sets ID to goal-0
                radio.name = 'goal'; // Ensures only one can be selected
                radio.value = "0";
    
                // Create label
                const label = document.createElement('label');
                label.htmlFor = radio.id;
                label.textContent = goalText;
                goalsDiv.appendChild(radio);
                goalsDiv.appendChild(label);
    
                // Append to the list
                goalsDiv.appendChild(goalContainer);
            }
        });
    
        removeGoalButton.addEventListener("click", function () {
            const selectedRadio = document.querySelector('input[name="goal"]:checked');
            if (selectedRadio) {
                const label = selectedRadio.nextSibling; // Get the label next to the radio button
                selectedRadio.remove(); // Remove only the radio button
                if (label && label.tagName === "LABEL") {
                    label.remove(); // Remove the label if it exists
                }
            } else {
                alert("Odaberite cilj za brisanje!");
            }
        });
    
        editGoalButton.addEventListener("click", function () {
            const selectedRadio = document.querySelector('input[name="goal"]:checked');
            if (selectedRadio) {
                const newText = prompt("Unesite novi tekst cilja:", selectedRadio.nextSibling.textContent);
                if (newText) {
                    selectedRadio.nextSibling.textContent = newText; // Update label text
                }
            } else {
                alert("Odaberite cilj za uređivanje!");
            }
        });


        //nacin realizacije
        // Get elements
    const addRealizationButton = document.getElementById("add-realization");
    const removeRealizationButton = document.getElementById("remove-realization");
    const editRealizationButton = document.getElementById("edit-realization");
    const realizationsDiv = document.getElementById("list-realizations");

    addRealizationButton.addEventListener("click", function () {
        const realizationText = prompt("Unesite način realizacije:");
        if (realizationText) {
            // Create radio button
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.id = `realization-0`; // Always sets ID to realization-0
            radio.name = 'realization'; // Ensures only one can be selected
            radio.value = "0";

            // Create label
            const label = document.createElement('label');
            label.htmlFor = radio.id;
            label.textContent = realizationText;

            // Append to the list
            realizationsDiv.appendChild(radio);
            realizationsDiv.appendChild(label);
        }
    });

    removeRealizationButton.addEventListener("click", function () {
        const selectedRadio = document.querySelector('input[name="realization"]:checked');
        if (selectedRadio) {
            const label = selectedRadio.nextSibling; // Get the label next to the radio button
            selectedRadio.remove(); // Remove only the radio button
            if (label && label.tagName === "LABEL") {
                label.remove(); // Remove the label if it exists
            }
        } else {
            alert("Odaberite način realizacije za brisanje!");
        }
    });

    editRealizationButton.addEventListener("click", function () {
        const selectedRadio = document.querySelector('input[name="realization"]:checked');
        if (selectedRadio) {
            const newText = prompt("Unesite novi tekst načina realizacije:", selectedRadio.nextSibling.textContent);
            if (newText) {
                selectedRadio.nextSibling.textContent = newText; // Update label text
            }
        } else {
            alert("Odaberite način realizacije za uređivanje!");
        }
    });

    //vrednovanje
    // Get elements
const addEvaluationButton = document.getElementById("add-evaluation");
const removeEvaluationButton = document.getElementById("remove-evaluation");
const editEvaluationButton = document.getElementById("edit-evaluation");
const evaluationDiv = document.getElementById("list-evaluation");

addEvaluationButton.addEventListener("click", function () {
    const evaluationText = prompt("Unesite način vrednovanja:");
    if (evaluationText) {
        // Create radio button
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `evaluation-0`; // Always sets ID to evaluation-0
        radio.name = 'evaluation'; // Ensures only one can be selected
        radio.value = "0";

        // Create label
        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = evaluationText;

        // Append to the list
        evaluationDiv.appendChild(radio);
        evaluationDiv.appendChild(label);
    }
});

removeEvaluationButton.addEventListener("click", function () {
    const selectedRadio = document.querySelector('input[name="evaluation"]:checked');
    if (selectedRadio) {
        const label = selectedRadio.nextSibling; // Get the label next to the radio button
        selectedRadio.remove(); // Remove only the radio button
        if (label && label.tagName === "LABEL") {
            label.remove(); // Remove the label if it exists
        }
    } else {
        alert("Odaberite način vrednovanja za brisanje!");
    }
});

editEvaluationButton.addEventListener("click", function () {
    const selectedRadio = document.querySelector('input[name="evaluation"]:checked');
    if (selectedRadio) {
        const newText = prompt("Unesite novi tekst načina vrednovanja:", selectedRadio.nextSibling.textContent);
        if (newText) {
            selectedRadio.nextSibling.textContent = newText; // Update label text
        }
    } else {
        alert("Odaberite način vrednovanja za uređivanje!");
    }
});
// Get elements
const addExpenseButton = document.getElementById("add-expense");
const removeExpenseButton = document.getElementById("remove-expense");
const editExpenseButton = document.getElementById("edit-expense");
const expensesDiv = document.getElementById("expenses");

addExpenseButton.addEventListener("click", function () {
    const expenseText = prompt("Unesite trošak:");
    if (expenseText) {
        // Create radio button
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.id = `expense-0`; // Always sets ID to expense-0
        radio.name = 'expense'; // Ensures only one can be selected
        radio.value = "0";

        // Create label
        const label = document.createElement('label');
        label.htmlFor = radio.id;
        label.textContent = expenseText;

        // Append to the list
        expensesDiv.appendChild(radio);
        expensesDiv.appendChild(label);
    }
});

removeExpenseButton.addEventListener("click", function () {
    const selectedRadio = document.querySelector('input[name="expense"]:checked');
    if (selectedRadio) {
        const label = selectedRadio.nextSibling; // Get the label next to the radio button
        selectedRadio.remove(); // Remove only the radio button
        if (label && label.tagName === "LABEL") {
            label.remove(); // Remove the label if it exists
        }
    } else {
        alert("Odaberite trošak za brisanje!");
    }
});

editExpenseButton.addEventListener("click", function () {
    const selectedRadio = document.querySelector('input[name="expense"]:checked');
    if (selectedRadio) {
        const newText = prompt("Unesite novi tekst troška:", selectedRadio.nextSibling.textContent);
        if (newText) {
            selectedRadio.nextSibling.textContent = newText; // Update label text
        }
    } else {
        alert("Odaberite trošak za uređivanje!");
    }
});
});

