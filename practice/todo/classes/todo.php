<?php

class Todo
{
    // todoリストに必要なメンバ変数
    private $title;
    private $content;
    private $flg;

    public function __construct($request_parameter)
    {
        $this->title   = $request_parameter['title'];
        $this->content = $request_parameter['content'];
        $this->flg     = 1;
    }

    public static function getTodo()
    {
        // データベースに接続
        global $dbh;
        $stmt  = $dbh->query('SELECT * FROM todolist WHERE flg = 1');
        $lists = $stmt->fetchAll();

        return $lists;
    }
    public static function getEndTodo()
    {
        global $dbh;
        $stmt  = $dbh->query('SELECT * FROM todolist WHERE flg = 0');
        $lists = $stmt->fetchAll();

        return $lists;
    }

    public static function update($id)
    {
        global $dbh;
        $query = $dbh->prepare("UPDATE todolist SET flg = 0 WHERE id = :id");
        $query->bindValue(":id", (int) $id);
        $query->execute();
    }

    public function insert($todo)
    {
        global $dbh;
        $prepare = $dbh->prepare('INSERT INTO todolist (title, content, flg) VALUES (:title, :content, :flg)');
        $prepare->bindValue(":title", $todo->title);
        $prepare->bindValue(":content", $todo->content);
        $prepare->bindValue(":flg", $todo->flg);
        $prepare->execute();
    }

    public static function delete ($id) {
        global $dbh;
        $prepare = $dbh->prepare ('DELETE FROM todolist WHERE id = :id');
        $prepare->bindValue (':id', (int) $id);
        $prepare->execute ();
    }
}
