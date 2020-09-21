<?php

require_once(__DIR__ . '/classes/Task.php');
require_once(__DIR__ . '/classes/TaskSheet.php');

$task_sheet = new TaskSheet();

$task1 = new Task('パスポート更新');
$task1->progress = 100;
$task_sheet->addTask($task1);

$task2 = new Task('食材の買い出し');
$task2->progress = 50;
$task_sheet->addTask($task2);

echo 'タスクリストを表示します。' . PHP_EOL;
$task_sheet->show();

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>todoアプリケーション</title>
</head>

<body>

</body>

</html>
