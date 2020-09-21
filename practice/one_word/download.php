<?php

const PASSWORD = 'root';
const DB_HOST  = 'mysql:host=localhost;dbname=bbs_sub;charset=utf8';
const DB_USER  = 'root';
const DB_PASS  = 'root';

session_start();

if (!empty($_GET['limit'])) {
    if ($_GET['limit'] === "2") {
        $limit = 2;
    } elseif ($_GET['limit'] === "30") {
        $limit = 30;
    }
}

$messages = [];

if (!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    // 出力の設定
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=メッセージデータ.csv");
    header("Content-Transfer-Encoding: binary");

    // データベースに接続
    try {
        $pdo = new PDO(DB_HOST, DB_USER, DB_PASS);

        if (!empty($limit)) {
            $sql = "SELECT * FROM messages ORDER BY date ASC LIMIT {$limit}";
        } else {
            $sql = "SELECT * FROM messages ORDER BY date ASC";
        }

        $res = $pdo->query($sql);

        if ($res) {
            $messages = $res->fetchAll();
        }
    } catch (PDOException $e) {
        print "接続エラー:{$e->getMessage()}";
    }

    //CSVデータを作成
    if (!empty($messages)) {

        $csv_data .= '"ID","タイトル","メッセージ","投稿日時"' . "\n";

        foreach ($messages as $message) {
            $csv_data .= '"' . $message['id'] . '","' . $message['title'] . '","' . $message['message'] . '","' . $message['date'] . "\"\n";
        }
    }

    // CSVデータを出力
    echo $csv_data;
} else {

    header('Location: ./admin.php');
}

return;
