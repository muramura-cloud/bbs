<?php

require_once('./config/init.php');
require_once('./app/Posts.php');

try {
    $posts = new Posts();
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました。{$e->getMessage()}");
}

$input_keys       = ['id', 'password', 'current_page_num', 'do_delete'];
$inputs           = get_inputs($_POST, $input_keys);
$id               = (int) $inputs['id'];
$current_page_num = (int) $inputs['current_page_num'];

try {
    $post = $posts->getRecordById($id);
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include('./404.php');
    exit;
}

$error_message = '';
if (is_empty($post['password'])) {
    $error_message = 'この投稿にはパスワードが設定されていないので削除出来ません。';
} elseif (is_empty($inputs['password'])) {
    $error_message = 'パスワードが入力されていません。投稿を削除するにはパスワードを入力してください。';
} elseif (!$posts::verifyPassword($inputs['password'], $post['password'])) {
    $error_message = 'パスワードが正しくありません。正しいパスワードを入力してください。';
}

if (empty($error_message) && !is_empty($inputs['do_delete'])) {
    if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name'])) {
        unlink('./images/' . $post['file_name']);
    }

    try {
        $posts->deleteRecordById($id);
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
        <h1>掲示板 レベル6</h1>
        <h2>投稿 削除</h2>
        <div class="post-box">
            <p class="title"><?php echo h($post['title']) ?></p>
            <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
            <?php if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name'])) : ?>
                <img src="images/<?php echo h($post['file_name']) ?>" alt="画像" width="400" height="300">
            <?php endif ?>
            <p class="created_at"><?php echo date('Y-m-d H:i', strtotime(h($post['created_at']))) ?></p>
            <?php if (empty($error_message)) : ?>
                <p><strong>この投稿を本当に削除しますか？</strong></p>
                <form action="" method="post">
                    <input name="id" type="hidden" value="<?php echo h($id) ?>">
                    <input name="password" type="hidden" value="<?php echo h($inputs['password']) ?>">
                    <input name="current_page_num" type="hidden" value="<?php echo h($current_page_num) ?>">
                    <input name="do_delete" type="submit" value="削除" class="confirm-delete-btn">
                </form>
            <?php else : ?>
                <p class="error_message"><?php echo h($error_message) ?></p>
                <?php if (!is_empty($post['password'])) : ?>
                    <form method="post" class="delete-form">
                        <input name="id" type="hidden" value="<?php echo h($id) ?>">
                        <input name="password" type="password" class="delete-password-form" placeholder="パスワード">
                        <input name="current_page_num" type="hidden" value="<?php echo h($current_page_num) ?>">
                        <input type="submit" value="削除" class="confirm-delete-btn">
                    </form>
                <?php endif ?>
            <?php endif ?>
            <a href="./?page_num=<?php echo h($current_page_num) ?>" class="back-btn">戻る</a>
        </div>
    </div>
</body>

</html>
