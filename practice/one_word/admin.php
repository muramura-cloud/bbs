<?php

const PASSWORD = 'root';
const DB_HOST  = 'mysql:host=localhost;dbname=bbs_sub;charset=utf8';
const DB_USER  = 'root';
const DB_PASS  = 'root';

date_default_timezone_get('Asia/Tokyo');

$error_messages = [];

session_start();

if (!empty($_GET['btn_logout'])) {
    unset($_SESSION['admin_login']);
}

if (!empty($_POST['btn_submit'])) {
    if (!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD) {
        $_SESSION['admin_login'] = true;
    } else {
        $error_messages[] = 'ログインに失敗しました。';
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

unset($_SESSION['success_insert_message']);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ひと言掲示板 管理者ページ </title>
    <link rel="stylesheet" href="./css/bbs_sub.css">
</head>

<body>
    <h1>ひと言掲示板 管理者ページ</h1>
    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo $error_message ?></p>
        <?php endforeach ?>
    <?php endif ?>
    <section>
        <?php if (!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) : ?>
            <form method="get" action="./download.php">
                <select name="limit">
                    <option value="">全て</option>
                    <option value="2">2件</option>
                    <option value="30">30件</option>
                </select>
                <input type="submit" name="btn_download" value="ダウンロード">
            </form>
            <?php if (!empty($messages)) : ?>
                <?php foreach ($messages as $message) : ?>
                    <article>
                        <div class="info">
                            <h2><?php echo $message['title'] ?></h2>
                            <time><?php echo date('Y年m月d日 H:i', strtotime($message['date'])) ?></time>
                            <p>
                                <!-- ?以降のmessage_id=<?php echo $message['id']; ?>がGETのパラメーターとなる。 -->
                                <a href="edit.php?message_id=<?php echo $message['id']; ?>">編集</a>
                                <a href="delete.php?message_id=<?php echo $message['id']; ?>">削除</a>
                            </p>
                        </div>
                        </div>
                        <p><?php echo nl2br($message['message']) ?></p>
                    </article>
                <?php endforeach ?>
            <?php endif ?>
            <form method="get">
                <input type="submit" name="btn_logout" value="ログアウト">
            </form>
        <?php else : ?>
            <form method="post">
                <div>
                    <label for="admin_password">ログインパスワード</label>
                    <input id="admin_password" type="password" name="admin_password" value="" class="password">
                </div>
                <input type="submit" name="btn_submit" value="ログイン">
            </form>
        <?php endif ?>
    </section>
</body>

</html>
