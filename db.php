<?php
$serverName = "DILAN\SQLEXPRESS"; // server name
$database = "UtilitySys_New"; // database name
$uid = ""; 
$pwd = ""; 

try {
    $connArr = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    if(empty($uid)) {
        $pdo = new PDO("sqlsrv:Server=$serverName;Database=$database", null, null, $connArr);
    } else {
        $pdo = new PDO("sqlsrv:Server=$serverName;Database=$database", $uid, $pwd, $connArr);
    }
} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
?>