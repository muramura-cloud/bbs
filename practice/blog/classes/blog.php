<?php

require_once('dbc.php');

class Blog extends Dbc
{
    // ここで$blog_nameを上書きすれば、Dbcクラスでの$table_nameが全てこの'blog'を使うことになる。
    protected $table_name = 'blog';

    //カテゴリー名をセットする。
    // このカテゴリー関数はブログだけで使うものなので、大元のDbcクラスではなく、Blogクラスで定義してあげる。
    public static function set_category_name($category)
    {
        if ((int) $category === 1) {
            return '日常';
        } elseif ((int) $category === 2) {
            return 'プログライング';
        } else {
            return 'その他';
        }
    }

    // ブログの内容が適切かどうかチェックする関数
    public function blog_validate($blogs)
    {
        if (empty($blogs['title'])) {
            exit('タイトルを入力してください。');
        }

        if (mb_strlen($blogs['title']) > 191) {
            exit('タイトルは191文字以下にしてください。');
        }

        if (empty($blogs['content'])) {
            exit('本文を入力してください。');
        }

        if (empty($blogs['category'])) {
            exit('カテゴリーは必須です。');
        }

        if (empty($blogs['publish_status'])) {
            exit('公開ステータスは必須です。');
        }
    }

    //ブログをデータベースに登録する。
    // このブログを作成する関数もブログでしか使わないので、ここで定義する。
    public function blog_create($blogs)
    {
        $dbh = $this->db_connect();

        $sql = "INSERT INTO {$this->table_name} (title,content,category,publish_status) VALUES (:title, :content, :category, :publish_status)";

        $dbh->beginTransaction();
        try {
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':title', $blogs['title']);
            $stmt->bindValue(':content', $blogs['content']);
            $stmt->bindValue(':category', $blogs['category'], PDO::PARAM_INT);
            $stmt->bindValue(':publish_status', $blogs['publish_status'], PDO::PARAM_INT);
            $stmt->execute();
            $dbh->commit();
            echo 'ブログを投稿しました。';
        } catch (PDOException $e) {
            $dbh->rollBack();
            exit($e->getMessage());
        }
    }

    public function blog_update($blogs)
    {
        $dbh = $this->db_connect();

        $sql = "UPDATE {$this->table_name} SET title = :title, content = :content, category = :category, publish_status = :publish_status WHERE id = :id";

        $dbh->beginTransaction();
        try {
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':title', $blogs['title']);
            $stmt->bindValue(':content', $blogs['content']);
            $stmt->bindValue(':category', $blogs['category'], PDO::PARAM_INT);
            $stmt->bindValue(':publish_status', $blogs['publish_status'], PDO::PARAM_INT);
            $stmt->bindValue(':id', $blogs['id'], PDO::PARAM_INT);
            $stmt->execute();
            $dbh->commit();
            echo 'ブログを更新しました。';
        } catch (PDOException $e) {
            $dbh->rollBack();
            exit($e->getMessage());
        }
    }
}
