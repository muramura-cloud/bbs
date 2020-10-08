<?php

require_once(__DIR__ . '/../lib/Database.php');

// プロパティに画像を保存する場所を指定すれば良いのかな。後、ゲッターとセッターを用意する。
// 画像を削除する機能deleteImg
// 画像のファイル名をユニークにする機能setUniqueId
// 画像を指定したディレクトリに保存する機能saveImg
class Posts extends Database
{
    protected $table_name = 'posts';

    // ここは絶対パスで指定すれば良い。
    private $img_path = '/bbs/images/';

    private $validation_rules = [
        'title' => [
            'name'  => 'タイトル',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 32],
            ],
        ],
        'message' => [
            'name'  => 'メッセージ',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 200],
            ],
        ],
        'img' => [
            'name'  => '画像',
            'rules' => [
                'allow_exts' => [
                    'jpeg' => 'image/jpeg',
                    'jpg'  => 'image/jpg',
                    'png'  => 'image/png',
                    'gif'  => 'image/gif',
                ],
                'max_size'   => 1024 * 1024, //1MB
            ],
        ],
        'password' => [
            'name'  => 'パスワード',
            'rules' => [
                'pattern'  => ['regex' => '/^[0-9]{4}$/', 'meaning' => '半角4桁の数字'],
            ],
        ],
    ];

    public function getValidationRules($validation_keys)
    {
        $rules = [];
        foreach ($validation_keys as $validation_key) {
            $rules[$validation_key] = $this->validation_rules[$validation_key];
        }

        return $rules;
    }

    public function addPost($values)
    {
        $values['created_at'] = date('Y-m-d H:i:s');
        if (isset($values['password'])) {
            $values['password'] = self::hashPassword($values['password']);
        }

        $this->insertRecord($values);
    }

    public static function verifyPassword($password, $hash_password)
    {
        return password_verify($password, $hash_password);
    }

    private static function hashPassword($password)
    {
        return !is_empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
    }
}
