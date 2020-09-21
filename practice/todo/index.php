<?php

require_once('./app/functions.php');
require_once('./classes/todo.php');

date_default_timezone_get('Asia/Tokyo');

try {
    $dbh = db_connect();
} catch (PDOException $e) {
    exit("接続失敗しました。 {$e->getMessage()}");
}

// postからデータが送られてきたら、データベースに保存する。
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $todo = new Todo($_POST);
    $todo->insert($todo);

    header('Location: ./');
    exit;
}

// 削除ボタンが押されたら、データベースの情報をアップデートする。
if (isset($_GET['delete_btn'])) {
    if ($_GET['flg'] === 1) {
        Todo::update((int) $_GET['id']);
    } else {
        Todo::delete((int) $_GET['id']);
    }

    header('Location: ./');
    exit;
}

// データベースからデータを取得
$todo_lists     = Todo::getTodo();
$end_todo_lists = Todo::getEndTodo();
?>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel='stylesheet' href='./css/todo.css'>
</head>

<body>
    <h1 class="title">todoリスト</h1>
    <h2>これからやること</h2>
    <ul class='ul'>
        <?php foreach ($todo_lists as $todo_item) : ?>
            <div class="todo-item">
                <h3><?php echo h($todo_item['title']) ?></h3>
                <p><?php echo h($todo_item['content']) ?></p>
                <form method='get'>
                    <input type='hidden' value='<?php echo $todo_item['id']; ?>' name='id'>
                    <input type='hidden' value='<?php echo $todo_item['flg']; ?>' name='flg'>
                    <input type='submit' name="delete_btn" value='終了' class='todo_button'>
                </form>
            </div>
        <?php endforeach ?>
    </ul>

    <h2>もうやったこと</h2>
    <ul class='ul'>
        <?php foreach ($end_todo_lists as $todo_item) : ?>
            <div class="todo-item">
                <h3><?php echo h($todo_item['title']) ?></h3>
                <p><?php echo h($todo_item['content']) ?></p>
                <form method='get'>
                    <input type='hidden' value='<?php echo $todo_item['id']; ?>' name='id'>
                    <input type='hidden' value='<?php echo $todo_item['flg']; ?>' name='flg'>
                    <input type='submit' name="delete_btn" value='削除' class='todo_button'>
                </form>
            </div>
        <?php endforeach ?>
    </ul>

    <form method="POST" class='form'>
        <input type="text" placeholder="タイトルを入力" name="title" class='title'>
        <textarea placeholder="内容を入力" name='content' class='content'></textarea>
        <input type="submit" value="追加" class='todo_button'>
    </form>
</body>

</html>
