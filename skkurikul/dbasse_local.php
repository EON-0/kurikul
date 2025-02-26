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
$con->set_charset('utf8');
// Check connection
if (!$con) {
die ("Connection failed: " . mysqli_connect_error());
}



?>