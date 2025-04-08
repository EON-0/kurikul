<?php
session_start();

$currentYear = date("Y");
$startYear = 2023;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['year'])) {
    $_SESSION['year'] = $_POST['year'];
    $message = "Session year set to: " . $_SESSION['year'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Select Year</title>
    <style>
        :root {
            --primary-color: #b9d7a1;
            --primary-hover-color: #bad7eb;
            --background-color: #f0f0f5;
            --text-color: #333;
            --font-family: Arial, sans-serif;
            --border-radius: 10px;
            --box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            --spacing-unit: 1rem;
            --input-padding: 0.6rem;
            --button-padding: 0.6rem;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-color);
        }

        .container {
            background-color: white;
            padding: calc(var(--spacing-unit) * 2);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 300px;
            text-align: center;
        }

        .container h1 {
            margin-bottom: var(--spacing-unit);
            font-size: 1.25rem;
        }

        .container label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-align: left;
        }

        .container select {
            width: 100%;
            padding: var(--input-padding);
            margin-bottom: var(--spacing-unit);
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .container button {
            padding: var(--button-padding);
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .container button:hover {
            background-color: var(--primary-hover-color);
        }

        .container p {
            margin-top: var(--spacing-unit);
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Odabir godine</h1>
        <form method="post" action="document.php">
            <label for="year">Godina:</label>
            <select name="year" id="year">
                <option value="-1" selected>Sve godine</option>
                <?php
                for ($year = $startYear; $year <= $currentYear; $year++) {
                    echo "<option value=\"$year\">$year</option>";
                }
                ?>
            </select>
            <button type="submit">Stvori dokument</button>
        </form>
    </div>
</body>

</html>