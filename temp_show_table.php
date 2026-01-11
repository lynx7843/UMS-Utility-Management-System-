<?php
$serverName = "ASHEN\SQLEXPRESS";
$database = "UtilitySys_New";
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

    $stmt = $pdo->query("SELECT * FROM UtilityTypes");
    $result = $stmt->fetchAll();
    echo json_encode($result);

} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
?>