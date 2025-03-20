<?php
// lokalno
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

/**
 * Prepare parameters for binding.
 * Converts any array values to JSON strings.
 */
function prepareParams($params) {
    $params = (array)$params;
    foreach ($params as &$param) {
        if (is_array($param)) {
            $param = json_encode($param);
        }
    }
    return $params;
}

/**
 * Helper function to fetch a single result.
 */
function fetchSingleResult($con, $sql, $params) {
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

/**
 * Function for INSERT, DELETE, and UPDATE queries.
 * Returns true on success, false on failure.
 */
function placeToDataBase($con, $sql, $params) {
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

/**
 * Helper function to fetch multiple results.
 */
function fetchMultipleResults($con, $sql, $params) {
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
?>
