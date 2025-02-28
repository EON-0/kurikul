<?php
define ('YOUR_ITPINGS_KEY', '12345');
define('DBHOST', 'localhost');
//define ('DBHOST', 'ourownserver.com:3306');
// The database name given by your Database Administrator
define('DBNAME', 'skkurikul');

// update with your own Database user account
define ('DBUSERNAME', 'root');
define('DBPASSWORD', '');
$con = mysqli_connect (DBHOST, DBUSERNAME, DBPASSWORD, DBNAME) ;
$con->set_charset('utf8mb4_croatian_ci');
// Check connection
if (!$con) {
die ("Connection failed: " . mysqli_connect_error());
}

// Helper function to fetch a single result
function fetchSingleResult($con,$sql, $params) {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        die(json_encode(["error" => mysqli_error($con)]));
    }

    if (!empty($params)) {
        $params = (array)$params;
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

// Helper function to fetch multiple results
function fetchMultipleResults($con,$sql, $params) {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        die(json_encode(["error" => mysqli_error($con)]));
    }

    if (!empty($params)) {
        $params = (array)$params;
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



?>