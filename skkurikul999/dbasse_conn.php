<?php
/*
define('YOUR_ITPINGS_KEY', '12345');

define('DBHOST', 'localhost');

define('DBNAME', 'zavrsni2024');

define('DBUSERNAME', 'skolskikurikulsql');

define('DBPASSWORD', 'ZQ&schTmN)IV');
*/

define('YOUR_ITPINGS_KEY', '12345');

define('DBHOST', 'localhost');

define('DBNAME', 'skkurikul');

define('DBUSERNAME', 'root');

define('DBPASSWORD', '');


$con = mysqli_connect(DBHOST, DBUSERNAME, DBPASSWORD, DBNAME);

//$con->set_charset('utf8mb4_croatian_ci');

// Check connection

if (!$con) {

    die("Connection failed: " . mysqli_connect_error());
}



//pomocna za parametre

function prepareParams($params)

{

    $params = (array)$params;

    foreach ($params as &$param) {

        if (is_array($param)) {

            $param = json_encode($param);
        }
    }

    return $params;
}



//pomocna za dobivanje jednog reda

function fetchSingleResult($con, $sql, $params)

{

    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {

        die(json_encode(["error" => mysqli_error($con)]));
    }



    $params = prepareParams($params);

    if (!empty($params)) {

        $types = '';

        foreach ($params as $param) {

            $types .= is_int($param) ? 'i' : (is_double($param) ? 'd' : 's');
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}



//pomocna za vise redova

function fetchMultipleResults($con, $sql, $params)

{

    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {

        die(json_encode(["error" => mysqli_error($con)]));
    }



    $params = prepareParams($params);

    if (!empty($params)) {

        $types = '';

        foreach ($params as $param) {

            $types .= is_int($param) ? 'i' : (is_double($param) ? 'd' : 's');
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }



    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $results = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $results[] = $row;
    }

    return $results;
}



//pomocna za unos i bazu

function placeToDataBase($con, $sql, $params)

{

    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {

        die(json_encode(["error" => mysqli_error($con)]));
    }



    $params = prepareParams($params);

    if (!empty($params)) {

        $types = '';

        foreach ($params as $param) {

            $types .= is_int($param) ? 'i' : (is_double($param) ? 'd' : 's');
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }



    $executeResult = mysqli_stmt_execute($stmt);

    return $executeResult ? true : false;
}
