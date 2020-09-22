<?php

require_once('./config/init.php');
require_once('./lib/Validation.php');

$input_keys       = ['id', 'password', 'current_page_num', 'title', 'message', 'confirm_edit_btn'];
$inputs           = get_inputs($_POST, $input_keys);
$id               = (int) $inputs['id'];
$current_page_num = (int) $inputs['current_page_num'];


try {
    $dbh = db_connect();
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました。{$e->getMessage()}");
}

try {
    $stmt = $dbh->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

if (is_empty($post['password'])) {
    $error_message = 'この投稿にはパスワードが設定されていないので編集出来ません。';
} elseif (is_empty($inputs['password'])) {
    $error_message = 'パスワードが入力されていません。投稿を編集するにはパスワードを入力してください。(パスワードは半角)';
} elseif (!password_verify($inputs['password'], $post['password'])) {
    $error_message = 'パスワードが正しくありません。再度正しいパスワードを入力してください。(パスワードは半角)';
}

$error_messages   = [];
$validation_rules = [
    'title' => [
        'name'  => 'タイトル',
        'rules' => [
            'required' => true,
            'length'   => ['min' => 10, 'max' => 32],
        ],
    ],
    'message' => [
        'name'  => 'メッセージ',
        'rules' => [
            'required' => true,
            'length'   => ['min' => 10, 'max' => 200],
        ],
    ],
];

if (empty($error_message) && !is_empty($inputs['confirm_edit_btn'])) {
    $validation     = new Validation;
    $error_messages = $validation->validate($inputs, $validation_rules);

    if (empty($error_messages)) {
        try {
            $stmt = $dbh->prepare('UPDATE posts SET title = :title, message = :message WHERE id = :id');
            $stmt->bindValue(':title', $inputs['title']);
            $stmt->bindValue(':message', $inputs['message']);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: ./?page_num={$current_page_num}");
            exit;
        } catch (PDOException $e) {
            exit('編集に失敗しました。');
        }
    }

    $post['title']   = $inputs['title'];
    $post['message'] = $inputs['message'];
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板 編集ページ</title>
    <link rel="stylesheet" href="./css/bbs.css">
</head>

<body>
    <div class="board">
        <h1>掲示板 レベル４</h1>
        <h2>投稿 編集</h2>
        <?php if (empty($error_message)) : ?>
            <?php foreach ($error_messages as $error_message) : ?>
                <p class="error_message"><?php echo $error_message ?></p>
            <?php endforeach ?>
            <form action="" method="post">
                <input name="title" type="text" class="title-form" placeholder="タイトルを入力してください。（<?php echo $validation_rules['title']['rules']['length']['min'] ?>文字以上<?php echo $validation_rules['title']['rules']['length']['max'] ?>文字以下）" value="<?php echo h($post['title']) ?>">
                <br><br>
                <textarea name="message" rows="4" cols="60" placeholder="メッセージを入力してください。（<?php echo $validation_rules['message']['rules']['length']['min'] ?>文字以上<?php echo $validation_rules['message']['rules']['length']['max'] ?>文字以下）"><?php echo h($post['message']) ?></textarea>
                <input name="id" type="hidden" value="<?php echo $id ?>">
                <input name="password" type="hidden" value="<?php echo $inputs['password'] ?>">
                <input name="current_page_num" type="hidden" value="<?php echo $current_page_num ?>">
                <input name="confirm_edit_btn" type="submit" value="編集" class="confirm-delete-btn">
            </form>
        <?php else : ?>
            <p class="error_message"><?php echo $error_message ?></p>
            <div class="post-box">
                <p class="title"><?php echo h($post['title']) ?></p>
                <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
                <p class="created_at"><?php echo date('Y-m-d H:i', strtotime($post['created_at'])) ?></p>
                <?php if (!is_empty($post['password'])) : ?>
                    <form action="" method="post" class="delete-form">
                        <input name="id" type="hidden" value="<?php echo $id ?>">
                        <input name="password" type="password" class="delete-password-form" placeholder="パスワード">
                        <input name="current_page_num" type="hidden" value="<?php echo $current_page_num ?>">
                        <input type="submit" value="編集" class="confirm-delete-btn">
                    </form>
                <?php endif ?>
            </div>
        <?php endif ?>
        <a href="./?page_num=<?php echo $current_page_num ?>" class="back-btn">戻る</a>
    </div>
</body>

</html>