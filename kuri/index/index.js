document.addEventListener('click', function (event) {
    if (event.target.classList.contains('ID_aktivnosti')) {
        const number = event.target.value; // Get the value of the clicked button
        sendNumber(number);
    }
});


function get_popisAktivnosti(userID) {
    $.ajax({
        url: 'server_aktivnosti.php', // Point to the PHP script
        method: 'GET',
        dataType: 'json',
        data: { User_ID: userID }, // Send the User_ID to PHP
        success: function(data) {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }

            // Process the data returned by PHP (activities)
            for (const id in data) {
                if (data.hasOwnProperty(id)) {
                    const activityName = data[id];
                    const button = `<button value='${id}' type='button' class='ID_aktivnosti'>${activityName}</button>`;
                    $('#scrollable-div').append(button);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

}
/*
<?php        
$sql = "SELECT DISTINCT Aktivnost.Naziv, Aktivnost.ID FROM Aktivnost JOIN Prava ON Aktivnost.ID = Prava.AktivnostID WHERE Prava.KorisnikID = ?";
$stmt = sqlsrv_query($conn, $sql, [$user_ID]);
if ($stmt === false) { die(print_r(sqlsrv_errors(), true)); }

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    print("<button type='button' value='{$row["ID"]}' class='ID_aktivnosti'  >{$row['Naziv']}</button>");
}
?>
*/
function sendNumber(number) {
    fetch('server_podaci.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ number: number }),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error(data);
                nazivElement.value = `<p>Error: ${data.error}</p>`;
                return;
            }
            const fields = {
                'name': data["Naziv"],
                'created': data["Kreirano"].date.split(' ')[0],
                'author': data["Autor"],
                'activity-type': data["Vrsta_Aktivnosti"],
                'status': data["Status"],
                'purpose': data["Namjena"],
                'carriers': data["Naziv"],
                'responsibility': "Neznam kaj je to",
                'timeline': data["Vremenik"],
                'evaluation': data["Naziv"],
                'expenses': data["Naziv"],
                'report': data["Izvjesce"]
            };

        for (const [id, value] of Object.entries(fields)) {
            const element = document.getElementById(id);
            if (element) {
                element.value = value;
            }
        }

        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            document.getElementById('naziv').value = `<p>Error: ${error.message}</p>`;
        });
}
