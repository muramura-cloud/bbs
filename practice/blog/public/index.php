<?php

require_once('../classes/blog.php');

$blog = new Blog();

$blog_data = $blog->get_all();

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>blog</title>
</head>

<body>
    <h1>ブログ一覧</h1>
    <p><a href="/bbs/practice/blog/public/form.html">新規作成</a></p>
    <table border="1" cellpadding="2">
        <tbody>
            <tr>
                <th>タイトル</th>
                <th>カテゴリ</th>
                <th>投稿日時</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <?php foreach ($blog_data as $column) : ?>
                <tr>
                    <td><?php echo $column['title'] ?></td>
                    <td><?php echo $blog->set_category_name($column['category']) ?></td>
                    <td><?php echo $column['created_at'] ?></td>
                    <td><a href="/bbs/practice/blog/public/detail.php?id=<?php echo $column['id'] ?>">詳細</a></td>
                    <td><a href="/bbs/practice/blog/public/update_form.php?id=<?php echo $column['id'] ?>">編集</a></td>
                    <td><a href="/bbs/practice/blog/classes/blog_delete.php?id=<?php echo $column['id'] ?>">削除</a></td>
                </tr>
            <?php endforeach ?>

        </tbody>
    </table>
</body>

</html>
