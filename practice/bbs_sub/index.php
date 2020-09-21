<?php

require_once('./app/functions.php');
require_once('./classes/Paginator.php');
require_once('./classes/Validation.php');

date_default_timezone_set('Asia/Tokyo');

try {
    $dbh = db_connect();
} catch (PDOException $e) {
    echo "データベースの接続に失敗しました。{$e->getMessage()}";
    exit;
}

$input_keys       = ['title', 'message', 'password'];
$inputs           = get_inputs($_POST, $input_keys);
$error_messages   = [];
$validation_rules = [
    'title' => [
        'name'  => 'タイトル',
        'rules' => [
            'required' => false,
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
    'password' => [
        'name'  => 'パスワード',
        'rules' => [
            'pattern'  => ['regex' => '/^[0-9]{4}+$/', 'meaning' => '半角4桁の数字'],
        ],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validation     = new Validation;
    $error_messages = $validation->validate($inputs, $validation_rules);

    if (empty($error_messages)) {
        try {
            $stmt = $dbh->prepare('INSERT INTO posts (title, message, created_at, password) VALUES (:title, :message, :created_at, :password)');
            $stmt->bindValue(':title', $inputs['title']);
            $stmt->bindValue(':message', $inputs['message']);
            $stmt->bindValue(':created_at', date('Y/m/d H:i:s'));
            $stmt->bindValue(':password', !is_empty($inputs['password']) ? password_hash($inputs['password'], PASSWORD_DEFAULT) : null);
            $stmt->execute();

            header('Location: ./');
            exit;
        } catch (PDOException $e) {
            exit('投稿に失敗しました。');
        }
    }
}

try {
    $stmt             = $dbh->query('SELECT COUNT(*) FROM posts');
    $total_post_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

$paginator = new Paginator($total_post_count);

if (isset($_GET['page_num'])) {
    $page_num     = (int) $_GET['page_num'];
    if ($page_num >= 1 && $page_num <= $paginator->getMaxPageNum()) {
        $paginator->setCurrentPageNum($page_num);
    } elseif ($page_num > $paginator->getMaxPageNum()) {
        $paginator->setCurrentPageNum($paginator->getMaxPageNum());
    }
}

$posts = [];
if ($total_post_count > 0) {
    try {
        $stmt = $dbh->prepare('SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $paginator->getOnePageItemCount(), PDO::PARAM_INT);
        $stmt->bindValue('offset', ($paginator->getCurrentPageNum() - 1) * $paginator->getOnePageItemCount(), PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
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
        <h1>掲示板 レベル4</h1>
        <h2>新規投稿</h2>
        <?php foreach ($error_messages as $error_message) : ?>
            <p class="error_message"><?php echo $error_message ?></p>
        <?php endforeach ?>
        <form action="" method="post">
            <input name="title" type="text" class="title-form" placeholder="タイトルを入力してください。（<?php echo $validation_rules['title']['rules']['length']['min'] ?>文字以上<?php echo $validation_rules['title']['rules']['length']['max'] ?>文字以下）" value="<?php echo h($inputs['title']) ?>">
            <br><br>
            <textarea name="message" rows="4" cols="60" placeholder="メッセージを入力してください。（<?php echo $validation_rules['message']['rules']['length']['min'] ?>文字以上<?php echo $validation_rules['message']['rules']['length']['max'] ?>文字以下）"><?php echo h($inputs['message']) ?></textarea>
            <br><br>
            <input name="password" type="password" class="password-form" placeholder="パスワードを入力してください。（<?php echo $validation_rules['password']['rules']['pattern']['meaning'] ?>）">
            <br><br>
            <input type="submit" value="投稿" class="submitBtn">
        </form>
        　
        <h2>投稿一覧</h2>
        <?php foreach ($posts as $post) : ?>
            <div class="post-box">
                <p class="title"><?php echo h($post['title']) ?></p>
                <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
                <p class="created_at"><?php echo date('Y-m-d H:i', strtotime($post['created_at'])) ?></p>
                <div class="edit-btn-box">
                    <form method="post" class="delete-form">
                        <input name="password" type="password" class="delete-password-form" placeholder="パスワードを入力してください。">
                        <input name="id" type="hidden" value="<?php echo $post['id'] ?>">
                        <input name="current_page_num" type="hidden" value="<?php echo $paginator->getCurrentPageNum() ?>">
                        <input type="submit" formaction="edit.php" value="編集" class="delete-btn">
                        <input type="submit" formaction="delete.php" value="削除" class="delete-btn">
                    </form>
                </div>
            </div>
        <?php endforeach ?>

        <?php if ($paginator->hasPrevPageNum()) : ?>
            <a href="?page_num=<?php echo $paginator->getPrevPageNum() ?>" class="page-btn">&lt;</a>
        <?php endif ?>
        <?php foreach ($paginator->getPageNums() as $page_num) : ?>
            <?php if ($page_num === $paginator->getCurrentPageNum()) : ?>
                <span class="page-btn current"><?php echo $page_num ?></span>
            <?php else : ?>
                <a href="?page_num=<?php echo $page_num ?>" class="page-btn"><?php echo $page_num ?></a>
            <?php endif ?>
        <?php endforeach ?>
        <?php if ($paginator->hasNextPageNum()) : ?>
            <a href="?page_num=<?php echo $paginator->getNextPageNum() ?>" class="page-btn">&gt;</a>
        <?php endif ?>
    </div>
</body>

</html>
