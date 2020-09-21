<?php

require_once('blog.php');

$blog = new Blog();

$blogs = $_POST;

$blog->blog_validate($blogs);

$blog->blog_create($blogs);

?>

<p><a href="../public/index.php">戻る</a></p>
