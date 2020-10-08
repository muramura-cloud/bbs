<?php

require_once('./config/init.php');
require_once('./lib/Validation.php');
require_once('./app/Posts.php');

try {
    $posts = new Posts();
} catch (PDOException $e) {
    exit("データベースの接続に失敗しました。{$e->getMessage()}");
}

$input_keys       = ['id', 'password', 'current_page_num', 'title', 'message', 'file_name', 'delete_img', 'do_edit'];
$inputs           = get_inputs($_POST, $input_keys);
$id               = (int) $inputs['id'];
$current_page_num = (int) $inputs['current_page_num'];

try {
    $post = $posts->getRecordById($id);
} catch (PDOException $e) {
    exit('投稿データの読み込みに失敗しました。');
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include('./404.php');
    exit;
}

$error_message = '';
if (is_empty($post['password'])) {
    $error_message = 'この投稿にはパスワードが設定されていないので削除出来ません。';
} elseif (is_empty($inputs['password'])) {
    $error_message = 'パスワードが入力されていません。投稿を削除するにはパスワードを入力してください。';
} elseif (!$posts::verifyPassword($inputs['password'], $post['password'])) {
    $error_message = 'パスワードが正しくありません。正しいパスワードを入力してください。';
}

$validate_error_messages = [];
$validation_rules        = $posts->getValidationRules(['title', 'message', 'img']);

if (empty($error_message) && !is_empty($inputs['do_edit'])) {
    if (is_uploaded_file($_FILES['img']['tmp_name'])) {
        $inputs['img'] = $_FILES['img'];
    }

    $validation              = new Validation($validation_rules);
    $validate_error_messages = $validation->validate($inputs);

    if (empty($validate_error_messages)) {
        // adv: 画像がどこにアップロードされているかも Posts クラスの責務の範囲内じゃない？
        if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name']) && !is_empty($inputs['delete_img'])) {
            unlink('./images/' . $inputs['file_name']);
            $inputs['file_name'] = null;
        } elseif (isset($inputs['img'])) {
            // 絶対にファイルネームがかぶらないようにする
            $inputs['file_name']  = uniqid();
            // adv: バリデーションは mime_type を見るようになったけど、こっちは拡張子をそのまま見てるので、test.exe(画像の拡張子をexeに変える) みたいなファイルができてしまいます。
            //      バリデーションで変なのがアップロードされないのは保証されているので大きな問題は起きないけど、
            //      ブラウザでアクセスした時に image/jpeg ではなく .exe の mime_type が返ってきてしまうので、たまに変になる可能性があります。
            $inputs['file_name'] .= '.' . pathinfo($inputs['img']['name'], PATHINFO_EXTENSION);
            // 実際に送られてきたファイルを指定したディレクトリに送信している。
            move_uploaded_file($inputs['img']['tmp_name'], './images/' . $inputs['file_name']);

            if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name'])) {
                unlink('./images/' . $post['file_name']);
            }
        }

        try {
            $posts->updateRecordById($id, [
                'title'     => $inputs['title'],
                'message'   => $inputs['message'],
                'file_name' => $inputs['file_name'],
            ]);
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
        <h1>掲示板 レベル6</h1>
        <h2>投稿 編集</h2>
        <?php if (empty($error_message)) : ?>
            <?php foreach ($validate_error_messages as $validate_error_message) : ?>
                <p class="error_message"><?php echo h($validate_error_message) ?></p>
            <?php endforeach ?>
            <form enctype="multipart/form-data" method="post">
                <input name="title" type="text" class="title-form" placeholder="タイトルを入力してください。（<?php echo h($validation_rules['title']['rules']['length']['min']) ?>文字以上<?php echo h($validation_rules['title']['rules']['length']['max']) ?>文字以下）" value="<?php echo h($post['title']) ?>">
                <br><br>
                <textarea name="message" rows="4" cols="60" placeholder="メッセージを入力してください。（<?php echo h($validation_rules['message']['rules']['length']['min']) ?>文字以上<?php echo h($validation_rules['message']['rules']['length']['max']) ?>文字以下）"><?php echo h($post['message']) ?></textarea>
                <br><br>
                <input name="img" type="file" class="img-input">
                <br><br>
                <?php if (!is_empty($post['file_name']) && file_exists('./images/' . $post['file_name'])) : ?>
                    <img src="./images/<?php echo h($post['file_name']) ?>" alt="画像" width="400" height="300">
                    <input name="delete_img" type="checkbox">画像を削除
                    <input name="file_name" type='hidden' value="<?php echo h($post['file_name']) ?>">
                <?php endif ?>
                <input name="id" type="hidden" value="<?php echo h($id) ?>">
                <input name="password" type="hidden" value="<?php echo h($inputs['password']) ?>">
                <input name="current_page_num" type="hidden" value="<?php echo h($current_page_num) ?>">
                <input name="do_edit" type="submit" value="編集" class="confirm-delete-btn">
            </form>
        <?php else : ?>
            <p class="error_message"><?php echo h($error_message) ?></p>
            <div class="post-box">
                <p class="title"><?php echo h($post['title']) ?></p>
                <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
                <?php if (!is_empty($post['file_name'])) : ?>
                    <img src="images/<?php echo $post['file_name'] ?>" alt="画像" width="300" height="300">
                <?php endif ?>
                <p class="created_at"><?php echo date('Y-m-d H:i', strtotime(h($post['created_at']))) ?></p>
                <?php if (!is_empty($post['password'])) : ?>
                    <form method="post" class="delete-form">
                        <input name="id" type="hidden" value="<?php echo h($id) ?>">
                        <input name="password" type="password" class="delete-password-form" placeholder="パスワード">
                        <input name="current_page_num" type="hidden" value="<?php echo h($current_page_num) ?>">
                        <input type="submit" value="編集" class="confirm-delete-btn">
                    </form>
                <?php endif ?>
            </div>
        <?php endif ?>
        <a href="./?page_num=<?php echo h($current_page_num) ?>" class="back-btn">戻る</a>
    </div>
</body>

</html>
