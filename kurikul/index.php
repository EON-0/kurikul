<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Školski Kurikulum</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="form-container">
        <div class="left-panel">
            <div class="field-group">
                <img src=".\logo\tsck.png" alt="Logo" class="logo"><br>

                <label for="vrste-aktivnosti">Vrste Aktivnosti:</label>
                <select id="vrste-aktivnosti">
                    <option>autorske</option>
                </select>
            </div>
            <div class="field-group">
                
                <label>
                    <input type="checkbox" id="aktualna-godina"> Sve / Aktualna godina
                </label><br>
                
                <div class="scrollable-div">
                    <button>Button 1</button>
                    <button>Button 2</button>
                    <button>Button 3</button>
                    <button>Button 4</button>
                    <button>Button 5</button>
                    <button>Button 6</button>
                    <button>Button 7</button>
                    <button>Button 8</button>
                    <button>Button 9</button>
                    <button>Button 10</button>
                 </div>
                 
            </div>

            <div class="field-group">
                <button>Nova Aktivnost</button>
                <button>Kopiraj</button>
            </div>
        </div>

        <div class="right-panel">
            <div class="field-group">
                <div>
                    <label for="kreirano">Kreirano:</label>
                    <input type="text" id="kreirano">
                </div>
                <div>
                    <label for="autor">Autor:</label>
                    <input type="text" id="autor">
                </div>
            </div>
            <div class="field-group">
                <div>
                    <label for="vrsta-aktivnosti">Vrsta Aktivnosti:</label>
                    <input type="text" id="vrsta-aktivnosti">
                </div>
                <div>
                    <label for="naziv">Naziv:</label>
                    <input type="text" id="naziv">
                </div>
            </div>
            <div class="field-group">
                <div>
                    <label for="status">Status:</label>
                    <input type="text" id="status">
                </div>
                <div>
                    <label for="ciljevi">Ciljevi:</label>
                    <textarea id="ciljevi" rows="2"></textarea>
                </div>
            </div>
            <div class="field-group">
                <div>
                    <label for="namjena">Namjena:</label>
                    <textarea id="namjena" rows="2"></textarea>
                </div>
                <div>
                    <label for="nacin-realizacije">Način realizacije:</label>
                    <textarea id="nacin-realizacije" rows="2"></textarea>
                </div>
            </div>
            <div class="field-group">
                <div>
                    <label for="nositelji">Nositelji:</label>
                    <textarea id="nositelji" rows="2"></textarea>
                </div>
                <div>
                    <label for="odgovornost">Odgovornost:</label>
                    <textarea id="odgovornost" rows="2"></textarea>
                </div>
            </div>
            <div class="field-group">
                <label for="vrijemnik">Vrijemnik:</label>
                <textarea id="vrijemnik" rows="2"></textarea>
            </div>
            <div class="field-group">
                <label for="vrednovanja">Način vrednovanja i rezultati:</label>
                <textarea id="vrednovanja" rows="3"></textarea>
            </div>
            <div class="field-group">
                <label for="troskovnik">Troškovnik:</label>
                <textarea id="troskovnik" rows="2"></textarea>
            </div>
            <div class="buttons">
                <button class = "button_obrisi">Obriši</button>
                <button>Odustani</button>
                <button>Spremi</button>               
                <button>Potvrdi</button>
                <button>Odbaci</button>
            </div>
        </div>
    </div>
</body>
</html>
