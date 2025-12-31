<?php
// POSTデータ取得
$punch_type = $_POST["punch_type"] ?? '';
$velocity   = $_POST["velocity"] ?? '';
$comment    = $_POST["comment"] ?? '';

if ($punch_type === '' || $velocity === '') {
    http_response_code(400);
    exit('punch_type and velocity are required');
}

// DB接続設定
try {
    $db_host = "mysql3112.db.sakura.ne.jp"; 
    $db_name = "renren_hook_db"; 
    $db_user = "renren_hook_db";
    $db_pass = "renren0613"; 

    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    exit('DBConnectError: ' . $e->getMessage());
}

// データ登録SQL作成
try {
    $sql = "INSERT INTO gs_hook_table(punch_type, velocity, comment, indate) VALUES(:punch_type, :velocity, :comment, NOW());";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':punch_type', $punch_type, PDO::PARAM_STR);
    $stmt->bindValue(':velocity',   $velocity,   PDO::PARAM_STR);
    $stmt->bindValue(':comment',    $comment,    PDO::PARAM_STR);
    $stmt->execute();
    
    echo "PUNCH_SAVED"; 
} catch (PDOException $e) {
    exit("SQLError: " . $e->getMessage());
}
?>