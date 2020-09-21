<?php

require('../classes/blog.php');

$blog = new Blog();

$result = $blog->get_by_id($_GET['id']);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブログ詳細</title>
</head>

<body>
    <h1>ブログ詳細</h1>
    <h2>タイトル: <?php echo $result['title'] ?></h2>
    <p>投稿日時: <?php echo $result['created_at'] ?></p>
    <p>カテゴリ: <?php echo $blog->set_category_name($result['category']) ?></p>
    <hr>
    <p>本文: <?php echo $result['content'] ?></p>
</body>

</html>
