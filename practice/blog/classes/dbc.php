<?php

require_once('../config/env.php');

// このクラスはデータベースに接続する時に使用するクラス
class Dbc
{
    protected $table_name;

    // インスタンスかした時に必ず呼ばれれる。
    // function __construct($table_name)
    // {
    //     $this->table_name = $table_name;
    // }

    //データベースに接続する。
    // この関数は自クラス内でしか使わないので、アクセス修飾子をprivateにする。
    protected function db_connect()
    {
        $db_host = DB_HOST;
        $db_name = DB_NAME;
        $db_user = DB_USER;
        $db_pass = DB_PASS;

        try {
            $dbh = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            echo "接続失敗 {$e->getMessage()}";
            exit;
        }
        return $dbh;
    }

    //データベースから全てのデータを取得
    // この関数はこのクラス外でも使うので、(index.phpとかで呼び出している。)publicにする。
    public function get_all()
    {
        $dbh    = $this->db_connect();
        // このようにコンストラクトで定義した変数($table_name)を使うことで汎用的にデータベースにアクセスできる。
        $sql    = "SELECT * FROM {$this->table_name}";
        // $sql    = 'SELECT * FROM blog';
        $stmt   = $dbh->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    //データベースから特定のデータを取得
    public function get_by_id($id)
    {
        if (empty($id)) {
            exit('idが不正です');
        }

        $dbh = $this->db_connect();

        // $stmt = $dbh->prepare('SELECT * FROM blog WHERE id = :id');
        // コンストラクで受けてくる変数でどのテーブルからデータを取ってくるのか変えることができる。
        $stmt = $dbh->prepare("SELECT * FROM {$this->table_name} WHERE id = :id");
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) {
            exit('ブログがありません');
        }

        return $result;
    }

    // データベースからデータを削除する関数
    public function delete($id)
    {
        if (empty($id)) {
            exit('idが不正です。');
        }

        $dbh = $this->db_connect();

        $stmt = $dbh->prepare("DELETE FROM {$this->table_name} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        echo 'ブログを削除しました。';
    }
}
