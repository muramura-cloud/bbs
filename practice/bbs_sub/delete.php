<?php

require_once('./app/functions.php');

try {
    $dbh = db_connect();
} catch (PDOException $e) {
    exit;
    echo "データベースの接続に失敗しました。{$e->getMessage()}";
}

$id               = (int) $_POST['id'];
$password         = $_POST['password'];
$current_page_num = (int) $_POST['current_page_num'];

try {
    $stmt = $dbh->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

// これと同じパスワード有無のチェックとパスワードが合ってるかどうかのチェックは編集ページでも同じだから一つの機能としてまとめておくべきかもしれない。
// まとめる際に、関数としてまとめるのか、もしくは、Validationクラスのメソッドとしてまとめるのか
// そこで、まとめるとしたら、下のように、「削除できません。」みたいな直接エラー分を入力するのは汎用的ではないのよね。
if (is_empty($post['password'])) {
    $error_message = 'この投稿にはパスワードが設定されていないので削除出来ません。';
} elseif (is_empty($password)) {
    $error_message = 'パスワードが入力されていません。投稿を削除するにはパスワードを入力してください。(パスワードは半角)';
} elseif (!password_verify($password, $post['password'])) {
    $error_message = 'パスワードが正しくありません。再度正しいパスワードを入力してください。(パスワードは半角)';
}


if (isset($_POST['confirm-delete-btn']) && password_verify($password, $post['password'])) {
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
                    <input name="password" type="hidden" value="<?php echo $password ?>">
                    <input name="current_page_num" type="hidden" value="<?php echo $current_page_num ?>">
                    <input name="confirm-delete-btn" type="submit" value="削除" class="confirm-delete-btn">
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
