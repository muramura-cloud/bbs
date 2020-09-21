<?php

require_once('./config/init.php');

try {
    $dbh = db_connect();
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました。{$e->getMessage()}");
}

$input_keys       = ['id', 'password', 'current_page_num', 'confirm_delete_btn'];
$inputs           = get_inputs($_POST, $input_keys);
$id               = (int) $inputs['id'];
$current_page_num = (int) $inputs['current_page_num'];

try {
    $stmt = $dbh->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

if (is_empty($post['password'])) {
    $error_message = 'この投稿にはパスワードが設定されていないので削除出来ません。';
} elseif (is_empty($inputs['password'])) {
    $error_message = 'パスワードが入力されていません。投稿を削除するにはパスワードを入力してください。';
} elseif (!password_verify($inputs['password'], $post['password'])) {
    $error_message = 'パスワードが正しくありません。正しいパスワードを入力してください。';
}

if (empty($error_message) && !is_empty($inputs['confirm_delete_btn'])) {
    try {
        $stmt = $dbh->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: ./?page_num={$current_page_num}");
        exit;
    } catch (PDOException $e) {
        exit('投稿データの削除に失敗しました。');
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板 削除ページ</title>
    <link rel="stylesheet" href="./css/bbs.css">
</head>

<body>
    <div class="board">
        <h1>掲示板 レベル４</h1>
        <h2>投稿 削除</h2>
        <div class="post-box">
            <p class="title"><?php echo h($post['title']) ?></p>
            <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
            <p class="created_at"><?php echo date('Y-m-d H:i', strtotime($post['created_at'])) ?></p>
            <?php if (empty($error_message)) : ?>
                <p><strong>この投稿を本当に削除しますか？</strong></p>
                <form action="" method="post">
                    <input name="id" type="hidden" value="<?php echo $id ?>">
                    <input name="password" type="hidden" value="<?php echo $inputs['password'] ?>">
                    <input name="current_page_num" type="hidden" value="<?php echo $current_page_num ?>">
                    <input name="confirm_delete_btn" type="submit" value="削除" class="confirm-delete-btn">
                </form>
            <?php else : ?>
                <p class="error_message"><?php echo $error_message ?></p>
                <?php if (!is_empty($post['password'])) : ?>
                    <form action="" method="post" class="delete-form">
                        <input name="id" type="hidden" value="<?php echo $id ?>">
                        <input name="password" type="password" class="delete-password-form" placeholder="パスワード">
                        <input name="current_page_num" type="hidden" value="<?php echo $current_page_num ?>">
                        <input type="submit" value="削除" class="confirm-delete-btn">
                    </form>
                <?php endif ?>
            <?php endif ?>
            <a href="./?page_num=<?php echo $current_page_num ?>" class="back-btn">戻る</a>
        </div>
    </div>
</body>

</html>
