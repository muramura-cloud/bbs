<?php

const DB_HOST = 'mysql:host=localhost;dbname=bbs_sub;charset=utf8';
const DB_USER = 'root';
const DB_PASS = 'root';

date_default_timezone_get('Asia/Tokyo');

$error_messages = [];
$clean          = [];

session_start();

if (empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ./admin.php");
}

if (!empty($_GET['message_id']) && empty($_POST['message_id'])) {
    $message_id = (int) htmlspecialchars($_GET['message_id'], ENT_QUOTES);
    try {
        $pdo = new PDO(DB_HOST, DB_USER, DB_PASS);

        $sql = "SELECT * FROM messages WHERE id = {$message_id}";
        $res = $pdo->query($sql);

        if ($res) {
            $selected_message = $res->fetch(PDO::FETCH_ASSOC);
        } else {
            header("Location: ./admin.php");
        }
    } catch (PDOException $e) {
        print "接続エラー:{$e->getMessage()}";
    }
} elseif (!empty($_POST['message_id'])) {
    $message_id = (int) htmlspecialchars($_POST['message_id'], ENT_QUOTES);

    try {
        $pdo = new PDO(DB_HOST, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $sql = "DELETE FROM messages WHERE id = {$message_id}";
        $res = $pdo->query($sql);

        if ($res) {
            header('Location: ./admin.php');
        }
    } catch (PDOException $e) {
        echo ("接続エラー {$e->getMessage()}");
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ひと言掲示板 管理ページ（投稿の削除）</title>
    <link rel="stylesheet" href="./css/bbs_sub.css">
</head>

<body>
    <h1>ひと言掲示板 管理ページ（投稿の削除）</h1>
    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo $error_message ?></p>
        <?php endforeach ?>
    <?php endif ?>
    <p class="text-confirm">以下の投稿を削除します。<br>よろしければ、「削除」ボタンを押してください。</p>
    <form method="post">
        <div>
            <label for="view_name">表示名</label>
            <input id="view_name" type="text" name="title" value="<?php if (!empty($selected_message)) {
                                                                        echo $selected_message['title'];
                                                                    } ?>" disabled>
        </div>
        <div>
            <label for="message">ひと言メッセージ</label>
            <textarea id="message" name="message" disabled><?php if (!empty($selected_message['message'])) {
                                                                echo $selected_message['message'];
                                                            } ?></textarea>
        </div>
        <a class="btn_cancel" href="admin.php">キャンセル</a>
        <input type="submit" name="btn_submit" value="削除">
        <input type="hidden" name="message_id" value="<?php echo $selected_message['id']; ?>">
    </form>
</body>

</html>
