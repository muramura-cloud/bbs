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

    if (empty($_POST['title'])) {
        $error_messages[] = '表示名を入力してください。';
    } else {
        $message_data['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
    }

    if (empty($_POST['message'])) {
        $error_messages[] = 'メッセージを入力してください。';
    } else {
        $message_data['message'] = htmlentities($_POST['message'], ENT_QUOTES);
    }

    if (empty($error_messages)) {
        try {
            $pdo = new PDO(DB_HOST, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = "UPDATE messages SET title = '{$message_data['title']}', message = '{$message_data['message']}' WHERE id = {$message_id}";
            $res = $pdo->query($sql);

            if ($res) {
                header('Location: ./admin.php');
            }
        } catch (PDOException $e) {
            print "接続エラー:{$e->getMessage()}";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ひと言掲示板 管理ページ（投稿の編集）</title>
    <link rel="stylesheet" href="./css/bbs_sub.css">
</head>

<body>
    <h1>ひと言掲示板 管理ページ（投稿の編集）</h1>
    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo $error_message ?></p>
        <?php endforeach ?>
    <?php endif ?>
    <form method="post">
        <div>
            <label for="view_name">表示名</label>
            <input id="view_name" type="text" name="title" value="<?php if (!empty($selected_message)) {
                                                                        echo $selected_message['title'];
                                                                    } ?>">
        </div>
        <div>
            <label for="message">ひと言メッセージ</label>
            <textarea id="message" name="message"><?php if (!empty($selected_message['message'])) {
                                                        echo $selected_message['message'];
                                                    } ?></textarea>
        </div>
        <a class="btn_cancel" href="admin.php">キャンセル</a>
        <input type="submit" name="btn_submit" value="更新">
        <input type="hidden" name="message_id" value="<?php echo $selected_message['id']; ?>">
    </form>
</body>

</html>
