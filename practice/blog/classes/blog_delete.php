<?php

require_once('blog.php');

$blog = new Blog();

$blog->delete((int) $_GET['id']);

?>

<p><a href="../public/index.php">戻る</a></p>
