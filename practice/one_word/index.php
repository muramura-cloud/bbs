<?php

const DB_HOST = 'mysql:host=localhost;dbname=bbs_sub;charset=utf8';
const DB_USER = 'root';
const DB_PASS = 'root';

date_default_timezone_get('Asia/Tokyo');

$error_messages = [];
$clean          = [];

session_start();

if (!empty($_POST['btn_submit'])) {
    if (empty($_POST['title'])) {
        $error_messages[] = 'タイトルを入力してください。';
    } else {
        $clean['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
        $clean['title'] = preg_replace('/\\r\\n|\\n|\\r/', '', $clean['title']);

        $_SESSION['title'] = $clean['title'];
    }

    if (empty($_POST['message'])) {
        $error_messages[] = 'メッセージを入力してください。';
    } else {
        $clean['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
    }

    if (empty($error_messages)) {
        try {
            $pdo = new PDO(DB_HOST, DB_USER, DB_PASS);

            $now_date = date("Y-m-d H:i:s");

            $sql = "INSERT INTO messages (title,message,date) VALUES ('{$clean['title']}','{$clean['message']}','{$now_date}')";

            $res = $pdo->query($sql);

            if ($res) {
                $_SESSION['success_message'] = 'メッセージを追加しました。';
            } else {
                $error_messages[] = 'メッセージの追加に失敗しました。';
            }

            header('Location: ./');
        } catch (PDOException $e) {
            print "接続エラー:{$e->getMessage()}";
        }
    }
}

try {
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASS);

    $sql = "SELECT * FROM messages ORDER BY date DESC";
    $res = $pdo->query($sql);

    if ($res) {
        $messages = $res->fetchAll();
    }
} catch (PDOException $e) {
    print "接続エラー:{$e->getMessage()}";
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ひと言掲示板</title>
    <link rel="stylesheet" href="./css/bbs_sub.css">
</head>

<body>
    <h1>ひと言掲示板</h1>
    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo $error_message ?></p>
        <?php endforeach ?>
    <?php endif ?>
    <?php if (!empty($_POST['btn_submit']) && !empty($_SESSION['success_message'])) : ?>
        <p class="success_message"><?php echo $_SESSION['success_message'] ?></p>
        <?php unset($_SESSION['success_message']) ?>
    <?php endif ?>
    <form method="post">
        <div>
            <label for="view_name">表示名</label>
            <input id="view_name" type="text" name="title" value="<?php if (!empty($_SESSION['title'])) {
                                                                        echo $_SESSION['title'];
                                                                    } ?>">
        </div>
        <div>
            <label for="message">ひと言メッセージ</label>
            <textarea id="message" name="message"></textarea>
        </div>
        <input type="submit" name="btn_submit" value="書き込む">
    </form>
    <hr>
    <section>
        <?php if (!empty($messages)) : ?>
            <?php foreach ($messages as $message) : ?>
                <article>
                    <div class="info">
                        <h2><?php echo $message['title'] ?></h2>
                        <time><?php echo date('Y年m月d日 H:i', strtotime($message['date'])) ?></time>
                    </div>
                    <p><?php echo nl2br($message['message']) ?></p>
                </article>
            <?php endforeach ?>
        <?php endif ?>
    </section>
</body>

</html>
