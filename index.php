<?php

require_once('./config/init.php');
require_once('./lib/Paginator.php');
require_once('./lib/Validation.php');
require_once('./app/Posts.php');

try {
    $posts = new Posts();
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました。{$e->getMessage()}");
}

$post_input_keys  = ['title', 'message', 'password'];
$post_inputs      = get_inputs($_POST, $post_input_keys);
$error_messages   = [];
$validation_rules = $posts->getValidationRules(['title', 'message', 'img', 'password']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (is_uploaded_file($_FILES['img']['tmp_name'])) {
        $post_inputs['img'] = $_FILES['img'];
    }

    $validation     = new Validation($validation_rules);
    $error_messages = $validation->validate($post_inputs);

    if (empty($error_messages)) {
        $post_inputs['file_name'] = null;
        if (isset($post_inputs['img'])) {
            $post_inputs['file_name']  = uniqid();
            $post_inputs['file_name'] .= '.' . pathinfo($post_inputs['img']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($post_inputs['img']['tmp_name'], './images/' . $post_inputs['file_name']);
        }

        try {
            $posts->addPost([
                'title'     => $post_inputs['title'],
                'message'   => $post_inputs['message'],
                'file_name' => $post_inputs['file_name'],
                'password'  => $post_inputs['password'],
            ]);

            header('Location: ./');
            exit;
        } catch (PDOException $e) {
            exit('投稿に失敗しました。');
        }
    }
}

try {
    $total_post_count = $posts->getRecordCount();
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

$paginator = new Paginator($total_post_count);

$get_input_keys = ['page_num'];
$get_inputs     = get_inputs($_GET, $get_input_keys);
if (!is_empty($get_inputs['page_num'])) {
    $paginator->setCurrentPageNum((int) $get_inputs['page_num']);
}

$records = [];
if ($total_post_count > 0) {
    try {
        $records = $posts->getRecords(
            [],
            [
                'created_at' => 'DESC',
            ],
            [
                'limit'  => $paginator->getItemCountPerPage(),
                'offset' => ($paginator->getCurrentPageNum() - 1) * $paginator->getItemCountPerPage(),
            ]
        );
    } catch (PDOException $e) {
        exit('投稿データの読み込みに失敗しました。');
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板 トップページ</title>
    <link rel="stylesheet" href="./css/bbs.css">
</head>

<body>
    <div class="board">
        <h1>掲示板 レベル6</h1>
        <h2>新規投稿</h2>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo h($error_message) ?></p>
        <?php endforeach ?>
        <form enctype="multipart/form-data" method="post">
            <input name="title" type="text" class="title-form" placeholder="タイトルを入力してください。（<?php echo h($validation_rules['title']['rules']['length']['min']) ?>文字以上<?php echo h($validation_rules['title']['rules']['length']['max']) ?>文字以下）" value="<?php echo h($post_inputs['title']) ?>">
            <br><br>
            <textarea name="message" rows="4" cols="60" placeholder="メッセージを入力してください。（<?php echo h($validation_rules['message']['rules']['length']['min']) ?>文字以上<?php echo h($validation_rules['message']['rules']['length']['max']) ?>文字以下）"><?php echo h($post_inputs['message']) ?></textarea>
            <br><br>
            <input name="img" type="file" class="img-input">
            <br><br>
            <input name="password" type="password" class="password-form" placeholder="パスワードを入力してください。（<?php echo h($validation_rules['password']['rules']['pattern']['meaning']) ?>）">
            <br><br>
            <input type="submit" value="投稿" class="submitBtn">
        </form>
        　
        <h2>投稿一覧</h2>
        <?php foreach ($records as $post) : ?>
            <div class="post-box">
                <p class="title"><?php echo h($post['title']) ?></p>
                <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
                <?php if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name'])) : ?>
                    <img src="./images/<?php echo $post['file_name'] ?>" alt="画像" width="400" height="300">
                <?php endif ?>
                <p class="created_at"><?php echo date('Y-m-d H:i', strtotime(h($post['created_at']))) ?></p>
                <form method="post" class="delete-form">
                    <input name="password" type="password" class="delete-password-form" placeholder="パスワードを入力してください。">
                    <input name="id" type="hidden" value="<?php echo h($post['id']) ?>">
                    <input name="current_page_num" type="hidden" value="<?php echo h($paginator->getCurrentPageNum()) ?>">
                    <input type="submit" value="編集" formaction="edit.php" class="delete-btn">
                    <input type="submit" value="削除" formaction="delete.php" class="delete-btn">
                </form>
            </div>
        <?php endforeach ?>

        <?php if ($paginator->hasPrevPageNum()) : ?>
            <a href="?page_num=<?php echo h($paginator->getPrevPageNum()) ?>" class="page-btn">&lt;</a>
        <?php endif ?>
        <?php foreach ($paginator->getPageNums() as $page_num) : ?>
            <?php if ($page_num === $paginator->getCurrentPageNum()) : ?>
                <span class="page-btn current"><?php echo h($page_num) ?></span>
            <?php else : ?>
                <a href="?page_num=<?php echo h($page_num) ?>" class="page-btn"><?php echo h($page_num) ?></a>
            <?php endif ?>
        <?php endforeach ?>
        <?php if ($paginator->hasNextPageNum()) : ?>
            <a href="?page_num=<?php echo h($paginator->getNextPageNum()) ?>" class="page-btn">&gt;</a>
        <?php endif ?>
    </div>
</body>

</html>
